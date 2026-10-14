<?php

namespace App\Http\Requests\Privacy;

use App\Models\Privacy\ConsentPurpose;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The guardian's decision on the parental-consent page (UU 27/2022 Pasal 25; PP 33/2026 Pasal 38).
 */
class ParentalConsentResponseRequest extends FormRequest
{
    /**
     * The signed link is checked by the controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['grant', 'decline'])],
            'guardian_name' => ['exclude_unless:decision,grant', 'required', 'string', 'min:2', 'max:120'],
            'guardian_declaration' => ['exclude_unless:decision,grant', 'accepted'],
            'required_consent' => ['exclude_unless:decision,grant', 'accepted'],
            'consents' => ['exclude_unless:decision,grant', 'nullable', 'array'],
            'consents.*' => ['string', Rule::exists(ConsentPurpose::class, 'key')],
            'reason' => ['exclude_unless:decision,decline', 'nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'decision' => 'keputusan',
            'guardian_name' => 'nama Anda',
            'guardian_declaration' => 'pernyataan orang tua/wali',
            'required_consent' => 'persetujuan pemrosesan data anak',
            'reason' => 'alasan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'guardian_declaration.accepted' => 'Mohon nyatakan bahwa Anda adalah orang tua atau wali sah anak ini.',
            'required_consent.accepted' => 'Persetujuan ini diperlukan agar anak dapat mengikuti kelas. Jika tidak setuju, pilih "Tolak pendaftaran".',
        ];
    }
}
