<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\Privacy\ParentalConsentRequest;
use App\Models\Profile;
use App\Models\User;
use App\Services\Privacy\ConsentService;
use App\Services\Privacy\ParentalConsentService;
use App\Services\Privacy\PolicyService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Self-registration for students and parents (staff accounts are created by admins).
 *
 * UU 27/2022: the privacy notice and terms are acknowledged and recorded (Pasal 21, 24);
 * optional consents are separate and unticked (Pasal 22 ayat (4)); a student younger than
 * setting('privacy.child_age_threshold') gets an inactive account until a parent/guardian
 * approves through an e-mailed link (Pasal 25 ayat (2); PP 33/2026 Pasal 38).
 */
class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly ConsentService $consents,
        private readonly PolicyService $policies,
        private readonly ParentalConsentService $parental,
    ) {
    }

    /**
     * Registration form, or the "closed" state when self-registration is off.
     */
    public function create(Request $request): View
    {
        $roles = RegisterRequest::allowedRoles();

        if (! setting('security.registration_enabled', true) || $roles === []) {
            return view('auth.register', ['closed' => true]);
        }

        $role = in_array($request->query('peran'), $roles, true) ? $request->query('peran') : $roles[0];

        return view('auth.register', [
            'closed' => false,
            'roles' => $roles,
            'role' => $role,
            'classes' => in_array('student', $roles, true) ? $this->openClasses() : collect(),
            'optional' => $this->optionalPurposes(),
            'threshold' => RegisterRequest::threshold(),
            'parentalRequired' => (bool) setting('privacy.require_parental_consent', true),
            'notice' => $this->policies->currentMeta('privacy_notice'),
            'terms' => $this->policies->currentMeta('terms'),
            'prefillEmail' => is_string($request->query('email')) ? mb_substr($request->query('email'), 0, 191) : null,
            'invitation' => is_string($request->query('undangan')) ? mb_substr($request->query('undangan'), 0, 128) : null,
        ]);
    }

    /**
     * Create the account, record acknowledgements and consents, and start parental consent
     * for children.
     *
     * @throws ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        abort_unless(setting('security.registration_enabled', true) && RegisterRequest::allowedRoles() !== [], 403, 'Pendaftaran mandiri sedang ditutup.');

        $role = $request->validated('role');
        $needsGuardian = $request->needsGuardian();

        $result = DB::transaction(function () use ($request, $role, $needsGuardian) {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
                'is_active' => ! $needsGuardian,
            ]);
            $user->assignRole($role);

            Profile::create([
                'user_id' => $user->id,
                'full_name' => $request->validated('name'),
                'phone_number' => $request->validated('phone'),
                'birth_date' => $role === 'student' ? $request->validated('birth_date') : null,
            ]);
            $user->load('profile');

            $class = $role === 'student' && $request->validated('class_id')
                ? $this->reserveSeat($user, (int) $request->validated('class_id'))
                : null;

            $this->policies->accept($user, $this->policies->currentMany(PolicyService::ACCEPTANCE_TYPES), 'registration', $request, $user);

            $consentRequest = null;
            if ($needsGuardian) {
                $consentRequest = $this->parental->create($user, $request->validated('guardian_name'), $request->validated('guardian_email'), $request->validated('guardian_relationship'));
            } else {
                $this->recordOptionalConsents($user, (array) $request->validated('consents', []), $request);
            }

            return compact('user', 'class', 'consentRequest');
        });

        /** @var User $user */
        $user = $result['user'];

        if ($result['consentRequest']) {
            $sent = $this->parental->send($result['consentRequest']);
            ConsentService::audit('registration.pending_parental', $user, ['request_id' => $result['consentRequest']->id]);

            return redirect()->route('register.success')->with('registration', [
                'state' => 'pending',
                'first_name' => strtok($user->name, ' '),
                'guardian_email' => ParentalConsentRequest::mask($result['consentRequest']->guardian_email),
                'email' => $user->email,
                'sent' => $sent,
                'class' => $result['class']?->name,
                'expires' => PolicyService::date($result['consentRequest']->fresh()->expires_at, true),
            ]);
        }

        if ($role === 'parent' && $request->validated('undangan')) {
            $this->parental->linkInvitedParent($user, $request->validated('undangan'));
        }

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        $message = 'Selamat datang, '.strtok($user->name, ' ').'! Akun Anda sudah aktif.';
        if ($result['class']) {
            $message .= ' Pendaftaran kelas '.$result['class']->name.' menunggu konfirmasi pembayaran dari admin.';
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

    /**
     * Status page after registration (parental consent pending, or a generic page).
     */
    public function success(Request $request): View
    {
        return view('auth.register-success', [
            'registration' => $request->session()->get('registration'),
            'dpoEmail' => (string) setting('privacy.dpo_email', ''),
        ]);
    }

    /**
     * Planned classes with free seats that start today or later.
     *
     * @return Collection<int, ClassName>
     */
    private function openClasses(): Collection
    {
        return ClassName::query()
            ->with('course:id,name')
            ->withSeatsTaken()
            ->withAvailableSeats()
            ->where('is_active', true)
            ->where('status', ClassName::STATUS_PLANNED)
            ->whereDate('start_date', '>=', today())
            ->orderBy('start_date')
            ->limit(50)
            ->get(['id', 'course_id', 'name', 'start_date', 'max_students', 'type', 'delivery_method']);
    }

    /**
     * Hold a seat with a pending enrollment (awaiting payment / admin confirmation).
     *
     * @throws ValidationException
     */
    private function reserveSeat(User $student, int $classId): ClassName
    {
        // Reason: lock the class row so two registrations cannot take the last seat together.
        $class = ClassName::query()->whereKey($classId)->lockForUpdate()->first();

        if (! $class || ! $class->is_active || $class->status !== ClassName::STATUS_PLANNED || ! $class->hasCapacity()) {
            throw ValidationException::withMessages(['class_id' => 'Kelas ini sudah penuh atau tidak lagi dibuka. Silakan pilih kelas lain.']);
        }

        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $class->course_id,
            'class_id' => $class->id,
            'enrollment_date' => now(),
            'status' => Enrollment::STATUS_PENDING,
            'payment_status' => 'unpaid',
            'notes' => 'Pendaftaran mandiri melalui situs.',
        ]);

        return $class;
    }

    /**
     * Optional, consent-based purposes offered at registration (not cookies; those use the banner).
     */
    private function optionalPurposes(): Collection
    {
        return $this->consents->purposes()
            ->filter(fn ($p) => $p->is_active && $p->isConsentBased() && ! $p->is_required && ! $p->isCookie() && $p->audience !== 'children')
            ->values();
    }

    /**
     * Grant only the optional purposes that were ticked and apply to the new account.
     */
    private function recordOptionalConsents(User $user, array $keys, Request $request): void
    {
        $allowed = $this->consents->purposesFor($user, true)->pluck('key')->all();

        foreach (array_unique($keys) as $key) {
            $purpose = $this->consents->purpose($key);

            if ($purpose && ! $purpose->is_required && ! $purpose->isCookie() && in_array($key, $allowed, true)) {
                $this->consents->grant($user, $key, 'registration', $user, ['form' => 'register'], $request);
            }
        }
    }
}
