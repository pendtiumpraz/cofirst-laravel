<?php

namespace App\Http\Requests\Privacy\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Publish a draft: effective date (local time) and whether everyone must accept again.
 */
class PublishPolicyRequest extends FormRequest
{
    /**
     * Route middleware requires 'manage legal documents'; checked again here.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('manage legal documents');
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'effective_at' => ['nullable', 'date'],
            'requires_reacceptance' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'effective_at' => 'tanggal berlaku',
            'requires_reacceptance' => 'persetujuan ulang',
        ];
    }
}
