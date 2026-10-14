<?php

namespace App\Http\Requests\Auth;

use App\Support\Auth\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;

/**
 * "Buat kata sandi baru" form reached from the reset link.
 */
class ResetPasswordRequest extends FormRequest
{
    /**
     * Anybody holding a valid token may reset (the broker checks the token).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', PasswordPolicy::rule()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email' => 'email',
            'password' => 'kata sandi baru',
            'password_confirmation' => 'konfirmasi kata sandi',
        ];
    }
}
