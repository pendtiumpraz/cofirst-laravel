<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\Auth\UserSessionService;
use App\Support\Auth\PasswordPolicy;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'request' => $request,
            'token' => (string) $request->route('token'),
            'email' => (string) $request->query('email', ''),
            'requirements' => PasswordPolicy::requirements(),
            'summary' => PasswordPolicy::summary(),
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * A successful reset rotates the remember token and ends every existing session
     * of the account, so whoever may have had access is signed out.
     *
     * @throws ValidationException
     */
    public function store(ResetPasswordRequest $request, UserSessionService $sessions): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request, $sessions) {
                $user->forceFill([
                    'password' => Hash::make($request->validated('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                $sessions->deleteOthers($user, null);

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Kata sandi berhasil diganti. Silakan masuk dengan kata sandi baru Anda.');
        }

        // Reason: an unknown email and an invalid token get the same message (no account enumeration).
        $message = $status === Password::RESET_THROTTLED
            ? trans('passwords.throttled')
            : 'Tautan atur ulang kata sandi tidak valid atau sudah kedaluwarsa. Minta tautan baru lalu coba lagi.';

        return back()->withInput($request->only('email'))->withErrors(['email' => $message]);
    }
}
