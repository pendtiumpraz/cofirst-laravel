<?php

namespace App\Http\Requests\Privacy;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Acceptance of new policy versions (re-acceptance page).
 */
class PolicyAcceptanceRequest extends FormRequest
{
    /**
     * Signed-in users accept for themselves (route is behind auth).
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
            'accept' => ['accepted'],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'accept.accepted' => 'Centang kotak persetujuan untuk melanjutkan, atau keluar bila Anda tidak setuju.',
        ];
    }
}
