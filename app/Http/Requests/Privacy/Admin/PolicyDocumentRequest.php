<?php

namespace App\Http\Requests\Privacy\Admin;

use App\Models\Privacy\PolicyDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create / edit a draft policy version.
 */
class PolicyDocumentRequest extends FormRequest
{
    /**
     * Route middleware requires 'manage legal documents'; checked again here.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('manage legal documents');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $policy = $this->route('policy');
        $type = $policy?->type ?? $this->input('type');

        return [
            'type' => [Rule::requiredIf(! $policy), 'nullable', Rule::in(array_keys(PolicyDocument::TYPES))],
            'version' => [
                'required', 'string', 'max:20', 'regex:/^[0-9A-Za-z][0-9A-Za-z.\-]*$/',
                Rule::unique('policy_documents', 'version')->where('type', $type)->ignore($policy?->id),
            ],
            'title' => ['required', 'string', 'max:150'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'body' => ['required', 'string', 'max:200000'],
            'changelog' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => 'jenis dokumen',
            'version' => 'nomor versi',
            'title' => 'judul',
            'summary' => 'ringkasan',
            'body' => 'isi dokumen',
            'changelog' => 'catatan perubahan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'version.unique' => 'Nomor versi ini sudah dipakai untuk jenis dokumen yang sama.',
            'version.regex' => 'Gunakan format versi seperti 1.1 atau 2.0.',
        ];
    }
}
