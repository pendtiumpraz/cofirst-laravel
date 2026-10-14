<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * Validation for Admin › Pengguna › Tambah.
 */
class StoreUserRequest extends UserRequest
{
    /** Only staff allowed to create accounts. */
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /** No target when creating. */
    protected function target(): ?User
    {
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->sharedRules() + [
            'email' => ['required', 'string', 'email:rfc', 'max:190', Rule::unique('users', 'email')],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'distinct', Rule::in($this->assignableRoles())],
            'send_invite' => ['boolean'],
            'password' => ['nullable', 'required_unless:send_invite,true', 'string', 'confirmed', $this->passwordRule()],
        ];
    }
}
