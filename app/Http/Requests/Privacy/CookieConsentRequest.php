<?php

namespace App\Http\Requests\Privacy;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Cookie choice from the banner (JSON) or the preferences page (form).
 */
class CookieConsentRequest extends FormRequest
{
    /**
     * Visitors and signed-in users may both set their own preference.
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
            'action' => ['nullable', 'string', 'in:accept_all,reject_optional,custom'],
            'functional' => ['nullable', 'boolean'],
            'analytics' => ['nullable', 'boolean'],
            'source' => ['nullable', 'string', 'in:banner,page'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'action' => 'pilihan',
            'functional' => 'cookie fungsional',
            'analytics' => 'cookie analitik',
        ];
    }
}
