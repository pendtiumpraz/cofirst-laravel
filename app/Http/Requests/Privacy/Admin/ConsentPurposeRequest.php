<?php

namespace App\Http\Requests\Privacy\Admin;

use App\Models\Privacy\ConsentPurpose;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create / edit a processing purpose (UU 27/2022 Pasal 20; PP 33/2026 Pasal 30, 67-68).
 */
class ConsentPurposeRequest extends FormRequest
{
    /**
     * Route middleware requires 'manage privacy'; checked again here.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('manage privacy');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $purpose = $this->route('purpose');

        return [
            'key' => [
                Rule::requiredIf(! $purpose), 'nullable', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('consent_purposes', 'key')->ignore($purpose?->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['required', Rule::in(array_keys(ConsentPurpose::CATEGORIES))],
            'legal_basis' => ['required', Rule::in(array_keys(ConsentPurpose::LEGAL_BASES))],
            'is_required' => ['nullable', 'boolean'],
            'applies_to' => ['required', 'array', 'min:1'],
            'applies_to.*' => [Rule::in(array_keys(ConsentPurpose::ROLES))],
            'audience' => ['required', Rule::in(array_keys(ConsentPurpose::AUDIENCES))],
            'data_used' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'bump_version' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'key' => 'kunci',
            'name' => 'nama tujuan',
            'description' => 'penjelasan',
            'category' => 'kategori',
            'legal_basis' => 'dasar pemrosesan',
            'applies_to' => 'berlaku untuk',
            'audience' => 'kelompok usia',
            'data_used' => 'data yang dipakai',
            'sort_order' => 'urutan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key.regex' => 'Kunci hanya boleh huruf kecil, angka dan garis bawah, diawali huruf (contoh: media_publication).',
        ];
    }
}
