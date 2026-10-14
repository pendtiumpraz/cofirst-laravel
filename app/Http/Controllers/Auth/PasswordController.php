<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Services\Auth\PrivacyBridge;
use App\Services\Auth\UserSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    /**
     * Update the user's password and sign out every other device.
     *
     * Reason: a password change is often a reaction to a suspected compromise, so a
     * stolen session or "remember me" cookie must not survive it.
     */
    public function update(UpdatePasswordRequest $request, UserSessionService $sessions): RedirectResponse
    {
        $user = $request->user();
        $password = $request->validated('password');

        $user->forceFill([
            'password' => Hash::make($password),
            // Rotated before logoutOtherDevices() re-issues this device's remember cookie.
            'remember_token' => Str::random(60),
        ])->save();

        $ended = $sessions->logoutOtherDevices($request, $password);
        $request->session()->regenerate();

        PrivacyBridge::audit('account.password.changed', $user, ['other_sessions_ended' => $ended]);

        $message = $ended > 0
            ? "Kata sandi berhasil diperbarui. Anda juga telah dikeluarkan dari {$ended} perangkat lain."
            : 'Kata sandi berhasil diperbarui.';

        return redirect()->to(route('profile.edit').'#keamanan')->with('success', $message);
    }
}
