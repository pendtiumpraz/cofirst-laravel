<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * DELETE /profile: account deletion must be confirmed with the current password.
 */
class DeleteAccountRequest extends FormRequest
{
    /**
     * Keeps the Breeze bag name used by the account-deletion form.
     *
     * @var string
     */
    protected $errorBag = 'userDeletion';

    /**
     * Signed-in users delete their own account only.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'current_password'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['password' => 'kata sandi'];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['password.current_password' => 'Kata sandi tidak sesuai.'];
    }
}
