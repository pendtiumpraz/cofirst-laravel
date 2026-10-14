<?php

namespace App\Http\Requests\Auth;

use App\Support\Auth\ActiveRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Choosing the active role ("Masuk sebagai …" / "Ganti peran").
 * Only a dashboard role the user actually holds is accepted.
 */
class RoleSelectionRequest extends FormRequest
{
    /**
     * Signed-in users only.
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
        return [
            'role' => ['required', 'string', Rule::in(ActiveRole::rolesOf($this->user()))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['role' => 'peran'];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.required' => 'Pilih peran yang ingin Anda gunakan.',
            'role.in' => 'Anda tidak memiliki peran tersebut.',
        ];
    }
}
