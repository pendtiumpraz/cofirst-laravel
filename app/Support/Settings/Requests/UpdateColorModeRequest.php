<?php

namespace App\Support\Settings\Requests;

use App\Support\Settings\UserPreferences;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST {color_mode} from the shell's colour-mode menu (resources/js/color-mode.js).
 * Allowed only while appearance.allow_user_color_mode is on.
 */
class UpdateColorModeRequest extends FormRequest
{
    /**
     * Signed-in users, and only while the owner lets users pick their colour mode.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && (bool) setting('appearance.allow_user_color_mode');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'color_mode' => ['required', Rule::in(array_keys(UserPreferences::COLOR_MODES))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['color_mode' => 'mode warna'];
    }
}
