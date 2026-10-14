<?php

namespace App\Http\Requests\Auth;

use App\Support\Auth\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Profil › Keamanan: change the password of the signed-in user.
 */
class UpdatePasswordRequest extends FormRequest
{
    /**
     * Errors go to a named bag so they render inside the password card only.
     *
     * @var string
     */
    protected $errorBag = 'updatePassword';

    /**
     * Signed-in users change their own password only.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', 'different:current_password', PasswordPolicy::rule()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'current_password' => 'kata sandi saat ini',
            'password' => 'kata sandi baru',
            'password_confirmation' => 'konfirmasi kata sandi',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.different' => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ];
    }
}
