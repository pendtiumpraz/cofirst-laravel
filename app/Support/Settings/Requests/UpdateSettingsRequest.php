<?php

namespace App\Support\Settings\Requests;

use App\Support\Settings\SettingsForm;
use App\Support\Settings\SettingsRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * Validates one settings group (route parameter {group}) with the rules
 * declared for each field in config/settings.php.
 */
class UpdateSettingsRequest extends FormRequest
{
    /**
     * Only users allowed to edit this group (security & privacy: superadmin).
     */
    public function authorize(): bool
    {
        return Gate::allows('settings.update', $this->group());
    }

    /**
     * Clean repeater rows, list lines and missing multi-value fields before validating.
     */
    protected function prepareForValidation(): void
    {
        $input = $this->input(SettingsRegistry::INPUT, []);

        $this->merge([
            SettingsRegistry::INPUT => app(SettingsForm::class)->prepare($this->group(), is_array($input) ? $input : []),
        ]);
    }

    /**
     * Rules from the registry.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return app(SettingsRegistry::class)->rules($this->group());
    }

    /**
     * Field labels for Indonesian validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return app(SettingsRegistry::class)->attributes($this->group());
    }

    /**
     * Messages that need more context than the generic translation.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            SettingsRegistry::INPUT.'.landing.cta_primary_url.regex' => 'Tautan harus diawali /, # atau https://.',
            SettingsRegistry::INPUT.'.landing.cta_secondary_url.regex' => 'Tautan harus diawali /, # atau https://.',
            SettingsRegistry::INPUT.'.appearance.custom_logo.mimes' => 'Logo harus berupa PNG, JPG atau WebP (SVG tidak diterima).',
            SettingsRegistry::INPUT.'.appearance.custom_logo_dark.mimes' => 'Logo harus berupa PNG, JPG atau WebP (SVG tidak diterima).',
            SettingsRegistry::INPUT.'.appearance.custom_logo.image' => 'Logo harus berupa gambar PNG, JPG atau WebP (SVG tidak diterima).',
            SettingsRegistry::INPUT.'.appearance.custom_logo_dark.image' => 'Logo harus berupa gambar PNG, JPG atau WebP (SVG tidak diterima).',
        ];
    }

    /**
     * The settings group from the route.
     */
    public function group(): string
    {
        return (string) $this->route('group');
    }
}
