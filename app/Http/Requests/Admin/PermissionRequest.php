<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

/**
 * Validation for custom permissions (superadmin only).
 *
 * Names follow the existing "verb object" style ("export enrollments") and may
 * not contain "|" or "," which spatie's permission middleware uses as separators.
 */
class PermissionRequest extends AdminRequest
{
    /** Superadmin only. */
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('superadmin');
    }

    /**
     * Lowercase and collapse whitespace.
     */
    protected function prepareForValidation(): void
    {
        $permission = $this->route('permission');
        if ($permission instanceof Permission && \App\Support\Admin\AccessControl::isSystemPermission($permission->name)) {
            $this->merge(['name' => $permission->name]);

            return;
        }

        if (is_string($this->input('name'))) {
            $this->merge(['name' => preg_replace('/\s+/', ' ', mb_strtolower(trim($this->input('name'))))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $permission = $this->route('permission');

        return [
            'name' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-z][a-z0-9 _.-]*$/',
                Rule::unique('permissions', 'name')->where('guard_name', 'web')->ignore($permission instanceof Permission ? $permission->id : null)],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama izin',
            'roles' => 'peran',
            'roles.*' => 'peran',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Gunakan huruf kecil, angka, spasi, titik, garis bawah atau tanda hubung (mis. "export enrollments").',
            'name.unique' => 'Nama izin ini sudah ada.',
        ];
    }
}
