<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Profil › Sesi aktif › "Keluar dari perangkat lain" (requires the current password).
 */
class LogoutOtherDevicesRequest extends FormRequest
{
    /**
     * Errors render inside the sessions dialog only.
     *
     * @var string
     */
    protected $errorBag = 'logoutOtherDevices';

    /**
     * Signed-in users end their own sessions only.
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
