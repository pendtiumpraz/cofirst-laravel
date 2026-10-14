<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * "Lupa kata sandi" form: the email to send the reset link to.
 */
class PasswordResetLinkRequest extends FormRequest
{
    /**
     * Guests only (guest middleware); anybody may ask for a link.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['email' => 'email'];
    }
}
