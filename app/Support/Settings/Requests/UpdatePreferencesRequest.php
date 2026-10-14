<?php

namespace App\Support\Settings\Requests;

use App\Support\Settings\UserPreferences;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the signed-in user's own preferences (Akun › Preferensi).
 * Theme and colour mode are only accepted while the owner allows users to choose them.
 */
class UpdatePreferencesRequest extends FormRequest
{
    /**
     * Any signed-in user may change their own preferences.
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
        $rules = [
            'locale' => ['required', Rule::in(array_keys(UserPreferences::LOCALES))],
            'density' => ['required', Rule::in(array_keys(UserPreferences::DENSITIES))],
            'text_size' => ['required', Rule::in(array_keys(UserPreferences::TEXT_SIZES))],
            'reduce_motion' => ['required', 'boolean'],
            'notifications' => ['nullable', 'array'],
            'notifications.email' => ['nullable', 'array'],
            'notifications.email.*' => ['boolean'],
        ];

        if (setting('appearance.allow_user_color_mode')) {
            $rules['color_mode'] = ['required', Rule::in(array_keys(UserPreferences::COLOR_MODES))];
        }

        if (setting('appearance.allow_user_theme')) {
            $rules['theme'] = ['required', Rule::in(['a', 'b'])];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'locale' => 'bahasa',
            'density' => 'kerapatan tampilan',
            'text_size' => 'ukuran teks',
            'reduce_motion' => 'kurangi animasi',
            'color_mode' => 'mode warna',
            'theme' => 'tema',
            'notifications.email.*' => 'notifikasi email',
        ];
    }
}
