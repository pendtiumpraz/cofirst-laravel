<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Enrollment;
use App\Models\FinancialTransaction;
use App\Models\User;
use App\Services\Admin\AdminAudit;
use App\Services\Admin\UserAccountService;
use App\Support\Admin\AccessControl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Admin › Pengguna: accounts, roles, profile, family links and lifecycle.
 *
 * Authorization lives in App\Policies\UserPolicy (superadmin-only management of
 * superadmin/admin accounts, no self-deactivation, last superadmin protected).
 */
class UserController extends Controller
{
    private const SORTS = ['name', 'email', 'created_at'];

    public function __construct(private readonly UserAccountService $accounts)
    {
    }

    /**
     * List users with search, role/status filters and sorting.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $status = $request->query('status');
        $sorted = in_array($request->query('sort'), self::SORTS, true);
        $sort = $sorted ? $request->query('sort') : 'created_at';
        $direction = $sorted ? ($request->query('direction') === 'desc' ? 'desc' : 'asc') : 'desc';

        $users = User::query()
            ->with(['roles:id,name', 'profile:id,user_id,birth_date'])
            ->when($status === 'archived', fn ($q) => $q->onlyTrashed())
            ->when($status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], trim((string) $request->query('search'))).'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->when($request->filled('role'), fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $request->query('role'))))
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        AdminAudit::log('user.list_viewed', null, [
            'filters' => array_keys(array_filter($request->only(['search', 'role', 'status']))),
            'rows' => $users->count(),
        ]);

        return view('admin.users.index', [
            'users' => $users,
            'stats' => $this->stats(),
            'roleOptions' => Role::orderBy('name')->pluck('name')->mapWithKeys(fn ($name) => [$name => AccessControl::roleLabel($name)])->all(),
        ]);
    }

    /**
     * JSON search used by the parent/child and roster pickers (names only).
     */
    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'role' => ['nullable', 'in:student,parent,teacher'],
            'exclude_class' => ['nullable', 'integer'],
        ]);

        $term = trim((string) ($validated['q'] ?? ''));
        $results = User::query()
            ->select(['id', 'name', 'email'])
            ->where('is_active', true)
            ->when($validated['role'] ?? null, fn ($q, $role) => $q->whereHas('roles', fn ($r) => $r->where('name', $role)))
            ->when($term !== '', function ($q) use ($term) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->when($validated['exclude_class'] ?? null, fn ($q, $classId) => $q->whereDoesntHave('enrollments', fn ($e) => $e
                ->where('class_id', $classId)->whereIn('status', Enrollment::SEAT_STATUSES)))
            ->orderBy('name')
            ->limit(20)
            ->get()
            // Reason: data minimisation — the picker needs a name and a masked hint only.
            ->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name, 'hint' => $this->maskEmail($u->email)]);

        return response()->json(['data' => $results]);
    }

    /**
     * Create form.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'roleOptions' => $this->roleOptions(request()->user()),
        ]);
    }

    /**
     * Store a new account.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = $this->accounts->create($request->accountData(), $request->file('photo'));

        $message = $request->boolean('send_invite')
            ? "Akun {$user->name} dibuat. Tautan untuk mengatur kata sandi dikirim ke emailnya."
            : "Akun {$user->name} berhasil dibuat.";

        return redirect()->route('admin.users.show', $user)->with('success', $message);
    }

    /**
     * Account detail with tabs (ringkasan, pembelajaran, keluarga, pembayaran, privasi).
     */
    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load(['roles:id,name', 'profile', 'children.roles:id,name', 'parents']);

        $enrollments = $user->hasRole('student')
            ? $user->enrollments()->with(['className:id,name,course_id,status', 'className.course:id,name'])->latest('enrollment_date')->get()
            : collect();

        $taughtClasses = $user->hasRole('teacher')
            ? $user->teachingClasses()->with('course:id,name')->withCount(['enrollments as seats_taken' => fn ($q) => $q->whereIn('status', Enrollment::SEAT_STATUSES)])->orderByDesc('class_names.start_date')->get()
            : collect();

        $studentIds = $user->hasRole('parent') ? $user->children->pluck('id')->all() : [$user->id];
        $transactions = FinancialTransaction::query()
            ->whereIn('student_id', $studentIds)
            ->with('student:id,name')
            ->latest('transaction_date')
            ->limit(8)
            ->get();
        $outstanding = (int) FinancialTransaction::query()->whereIn('student_id', $studentIds)->payments()->pending()->sum('amount');

        AdminAudit::log('user.viewed', $user);

        return view('admin.users.show', compact('user', 'enrollments', 'taughtClasses', 'transactions', 'outstanding'));
    }

    /**
     * Edit form.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $user->load(['roles:id,name', 'profile', 'children:id,name,email', 'parents:id,name,email']);
        $actor = request()->user();

        return view('admin.users.edit', [
            'user' => $user,
            'roleOptions' => $this->roleOptions($actor),
            'canChangeRoles' => $actor->can('changeRoles', $user),
            'isSelf' => $actor->is($user),
        ]);
    }

    /**
     * Update an account.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $result = $this->accounts->update($user, $request->accountData(), $request->file('photo'), $request->boolean('remove_photo'));

        $message = "Perubahan akun {$user->name} disimpan.";
        if ($result['unlinked'] > 0) {
            $message .= " {$result['unlinked']} tautan keluarga dilepas karena perannya berubah.";
        }

        return redirect()->route('admin.users.show', $user)->with('success', $message);
    }

    /**
     * Archive (soft delete) an account.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->accounts->archive($user);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$user->name} diarsipkan. Riwayat pembayaran, laporan dan sertifikat tetap tersimpan.");
    }

    /**
     * Restore an archived account (it stays inactive).
     */
    public function restore(User $user): RedirectResponse
    {
        $this->authorize('restore', $user);

        $this->accounts->restore($user);

        return redirect()->route('admin.users.show', $user)
            ->with('success', "Akun {$user->name} dipulihkan dan masih nonaktif. Aktifkan bila sudah siap dipakai.");
    }

    /**
     * Activate / deactivate an account (deactivation ends its sessions).
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        $this->authorize('toggleStatus', $user);

        $this->accounts->setActive($user, ! $user->is_active);

        return back()->with('success', $user->is_active
            ? "Akun {$user->name} diaktifkan."
            : "Akun {$user->name} dinonaktifkan dan semua sesinya diakhiri.");
    }

    /**
     * Send a password set/reset link to the account's email.
     */
    public function sendPasswordLink(User $user): RedirectResponse
    {
        $this->authorize('sendPasswordLink', $user);

        return $this->accounts->sendPasswordLink($user)
            ? back()->with('success', 'Tautan atur ulang kata sandi telah dikirim ke email pengguna.')
            : back()->with('error', 'Tautan belum dapat dikirim. Coba lagi beberapa menit lagi.');
    }

    /**
     * Link a parent and a student from either account's page.
     */
    public function attachFamily(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $request->validate([
            'related_id' => ['required', 'integer', 'exists:users,id'],
            'relationship' => ['nullable', 'in:ayah,ibu,wali'],
            'guardian_verified' => ['accepted'],
        ], [
            'guardian_verified.accepted' => 'Konfirmasi bahwa hubungan orang tua/wali sudah diverifikasi.',
        ], ['related_id' => 'akun', 'relationship' => 'hubungan']);

        $related = User::findOrFail($request->integer('related_id'));
        [$parent, $student] = $this->familyPair($user, $related);

        if (! $parent) {
            return redirect()->to(route('admin.users.show', $user).'#keluarga')
                ->withErrors(['related_id' => 'Tautan hanya bisa dibuat antara akun orang tua dan akun siswa.']);
        }

        $linked = $this->accounts->linkFamily($parent, $student, [
            'relationship' => $request->input('relationship'),
            'verification' => 'admin_confirmed',
        ]);

        return redirect()->to(route('admin.users.show', $user).'#keluarga')->with($linked ? 'success' : 'info', $linked
            ? "{$parent->name} kini tertaut sebagai orang tua/wali {$student->name}."
            : 'Kedua akun sudah tertaut sebelumnya.');
    }

    /**
     * Remove a parent–student link.
     */
    public function detachFamily(User $user, User $related): RedirectResponse
    {
        $this->authorize('update', $user);

        [$parent, $student] = $this->familyPair($user, $related);
        if ($parent) {
            $this->accounts->unlinkFamily($parent, $student);
        }

        return redirect()->to(route('admin.users.show', $user).'#keluarga')->with('success', 'Tautan keluarga dilepas.');
    }

    /**
     * Work out which of the two accounts is the parent and which the student.
     *
     * @return array{0: User|null, 1: User|null}
     */
    private function familyPair(User $a, User $b): array
    {
        if ($a->hasRole('parent') && $b->hasRole('student') && ! $a->is($b)) {
            return [$a, $b];
        }
        if ($a->hasRole('student') && $b->hasRole('parent') && ! $a->is($b)) {
            return [$b, $a];
        }

        return [null, null];
    }

    /**
     * Role checkboxes the actor may use: name => [label, description].
     *
     * @return array<string, array{label: string, description: string}>
     */
    private function roleOptions(User $actor): array
    {
        $names = Role::where('guard_name', 'web')->orderBy('id')->pluck('name')->all();

        return collect(AccessControl::assignableRoles($actor, $names))
            ->mapWithKeys(fn ($name) => [$name => [
                'label' => AccessControl::roleLabel($name),
                'description' => AccessControl::ROLE_DESCRIPTIONS[$name] ?? 'Peran kustom.',
            ]])->all();
    }

    /**
     * Headline counts for the list page (one grouped query for roles).
     *
     * @return array<string, int>
     */
    private function stats(): array
    {
        $byRole = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->join('users', 'users.id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.model_type', User::class)
            ->whereNull('users.deleted_at')
            ->groupBy('roles.name')
            ->selectRaw('roles.name as role, count(*) as total')
            ->pluck('total', 'role');

        return [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'students' => (int) ($byRole['student'] ?? 0),
            'teachers' => (int) ($byRole['teacher'] ?? 0),
            'parents' => (int) ($byRole['parent'] ?? 0),
            'archived' => User::onlyTrashed()->count(),
        ];
    }

    /**
     * Mask an email for pickers: "andi.pratama@x.id" → "an••••@x.id".
     */
    private function maskEmail(?string $email): string
    {
        if (! $email || ! str_contains($email, '@')) {
            return '';
        }

        [$local, $domain] = explode('@', $email, 2);

        return Str::substr($local, 0, 2).'••••@'.$domain;
    }
}
