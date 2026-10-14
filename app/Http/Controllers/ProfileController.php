<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\DeleteAccountRequest;
use App\Http\Requests\Profile\LogoutOtherDevicesRequest;
use App\Http\Requests\Profile\ProfilePhotoRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\Auth\PrivacyBridge;
use App\Services\Auth\UserSessionService;
use App\Support\Auth\ActiveRole;
use App\Support\Auth\PasswordPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

/**
 * Profil saya: personal information, photo, password, active sessions, account deletion.
 */
class ProfileController extends Controller
{
    /** Roles that may not delete their own account (they hold obligations for others). */
    private const STAFF_ROLES = ['superadmin', 'admin', 'finance'];

    /**
     * Display the user's profile page.
     */
    public function edit(Request $request, UserSessionService $sessions): View
    {
        $user = $request->user()->loadMissing(['profile', 'roles']);
        $timezone = function_exists('setting') ? (string) setting('general.timezone', 'Asia/Jakarta') : 'Asia/Jakarta';

        return view('profile.edit', [
            'user' => $user,
            'profile' => $user->profile,
            'roles' => array_map(fn (string $role) => ActiveRole::label($role), ActiveRole::rolesOf($user)),
            'memberSince' => $user->created_at?->copy()->setTimezone($timezone)->locale('id'),
            'canChangeEmail' => ProfileUpdateRequest::canChangeEmail($user),
            'canChangeBirthDate' => ProfileUpdateRequest::canChangeBirthDate($user),
            'passwordRequirements' => PasswordPolicy::requirements(),
            'passwordSummary' => PasswordPolicy::summary(),
            'sessionsSupported' => $sessions->supported(),
            'sessions' => $sessions->forUser($user, $request->session()->getId()),
            'privacyUrl' => Route::has('account.privacy.index') ? route('account.privacy.index') : null,
            'preferencesUrl' => Route::has('account.preferences') ? route('account.preferences') : null,
            'photoMaxKb' => ProfilePhotoRequest::MAX_KB,
        ]);
    }

    /**
     * Update the user's profile information (users + profiles in one transaction).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        DB::transaction(function () use ($user, $data) {
            $user->name = $data['name'];

            if (array_key_exists('email', $data)) {
                $user->email = Str::lower($data['email']);
            }

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            $profile = [
                'full_name' => $data['name'],
                'phone_number' => $data['phone_number'] ?? null,
                'gender' => $data['gender'] ?? null,
                'address' => $data['address'] ?? null,
                'bio' => $data['bio'] ?? null,
            ];

            // Reason: protected fields are absent from validated() and must stay untouched.
            if (array_key_exists('birth_date', $data)) {
                $profile['birth_date'] = $data['birth_date'];
            }

            $user->profile()->updateOrCreate(['user_id' => $user->id], $profile);
        });

        return Redirect::route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Sign the user out of every other device ("Keluar dari perangkat lain").
     */
    public function destroyOtherSessions(LogoutOtherDevicesRequest $request, UserSessionService $sessions): RedirectResponse
    {
        $ended = $sessions->logoutOtherDevices($request, $request->validated('password'));

        PrivacyBridge::audit('account.sessions.revoked', $request->user(), ['sessions_ended' => $ended]);

        $message = $ended > 0
            ? "Anda telah keluar dari {$ended} perangkat lain."
            : 'Semua perangkat lain telah dikeluarkan. Sesi “ingat saya” di perangkat lain juga tidak berlaku lagi.';

        return redirect()->to(route('profile.edit').'#sesi')->with('success', $message);
    }

    /**
     * Delete the user's account (password confirmed).
     *
     * Account erasure under UU PDP belongs to the privacy module: when its
     * ErasureService exists the account is anonymised there (keeping records the law
     * requires); otherwise the account is archived (soft delete). Staff accounts cannot
     * delete themselves.
     */
    public function destroy(DeleteAccountRequest $request, UserSessionService $sessions): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasAnyRole(self::STAFF_ROLES)) {
            return back()->withErrors([
                'password' => 'Akun staf tidak dapat dihapus sendiri. Hubungi superadmin untuk menonaktifkan akun Anda.',
            ], 'userDeletion');
        }

        if (PrivacyBridge::erasureAvailable()) {
            try {
                $handled = PrivacyBridge::erase($user);
            } catch (Throwable $e) {
                report($e);
                $handled = false;
            }

            if (! $handled) {
                return back()->withErrors([
                    'password' => 'Akun belum dapat dihapus otomatis. Ajukan permintaan penghapusan melalui Pusat Privasi.',
                ], 'userDeletion');
            }
        } else {
            $this->archive($user, $sessions);
        }

        PrivacyBridge::audit('account.deleted_by_owner', $user);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Akun Anda telah dihapus. Terima kasih telah belajar bersama Coding First.');
    }

    /**
     * Fallback when the privacy module is not installed: archive (soft delete) the
     * account and end every session so it cannot be used again.
     */
    private function archive(User $user, UserSessionService $sessions): void
    {
        DB::transaction(function () use ($user) {
            $user->forceFill(['is_active' => false, 'remember_token' => Str::random(60)])->save();
            $user->delete();
        });

        $sessions->deleteOthers($user, null);
    }
}
