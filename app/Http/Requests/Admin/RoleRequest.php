<?php

namespace App\Http\Requests\Admin;

use App\Support\Admin\AccessControl;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/**
 * Validation for roles (superadmin only; route middleware role:superadmin).
 *
 * Names are lowercase slugs because spatie's role middleware splits on "|"
 * and ","; system role names cannot be changed.
 */
class RoleRequest extends AdminRequest
{
    /** Superadmin only. */
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('superadmin');
    }

    /**
     * Lowercase the name; keep the system name when editing a system role.
     */
    protected function prepareForValidation(): void
    {
        $role = $this->route('role');
        if ($role instanceof Role && AccessControl::isSystemRole($role->name)) {
            $this->merge(['name' => $role->name]);

            return;
        }

        if (is_string($this->input('name'))) {
            $this->merge(['name' => mb_strtolower(trim($this->input('name')))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[a-z][a-z0-9_-]*$/',
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role instanceof Role ? $role->id : null)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama peran',
            'permissions' => 'izin',
            'permissions.*' => 'izin',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Nama peran memakai huruf kecil, angka, garis bawah atau tanda hubung, diawali huruf (mis. koordinator_kelas).',
            'name.unique' => 'Nama peran ini sudah ada.',
        ];
    }
}
