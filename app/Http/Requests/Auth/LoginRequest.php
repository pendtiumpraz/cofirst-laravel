<?php

namespace App\Http\Requests\Auth;

use App\Http\Middleware\EnsureUserIsActive;
use App\Models\User;
use App\Support\Auth\ActiveRole;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login form: email + password (+ "ingat saya").
 *
 * Throttled per email + IP with the limit from setting('security.login_max_attempts')
 * per minute. Deactivated accounts and accounts without a dashboard role are refused
 * (after the password is verified, so the refusal does not reveal which emails exist).
 */
class LoginRequest extends FormRequest
{
    /** Seconds a failed attempt counts against the limit ("per menit"). */
    private const DECAY_SECONDS = 60;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Indonesian attribute names for validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'email',
            'password' => 'kata sandi',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $refusal = null;

        // Reason: attemptWhen only runs the callback after the password is verified, so an
        // inactive/role-less account is reported only to someone who knows its password.
        $authenticated = Auth::attemptWhen(
            $this->only('email', 'password'),
            function (User $user) use (&$refusal) {
                if ($user->is_active === false) {
                    $refusal = EnsureUserIsActive::MESSAGE;

                    return false;
                }

                if (ActiveRole::rolesOf($user) === []) {
                    $refusal = 'Akun Anda belum memiliki peran. Hubungi admin Coding First agar akses Anda diatur.';

                    return false;
                }

                return true;
            },
            $this->boolean('remember')
        );

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey(), self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => $refusal ?? trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $this->maxAttempts())) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Allowed failed attempts per minute (setting security.login_max_attempts, 3–20).
     */
    public function maxAttempts(): int
    {
        $value = function_exists('setting') ? (int) setting('security.login_max_attempts', 5) : 5;

        return max(3, min(20, $value));
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
