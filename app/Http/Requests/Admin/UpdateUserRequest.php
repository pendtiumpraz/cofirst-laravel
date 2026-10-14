<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * Validation for Admin › Pengguna › Ubah.
 *
 * Roles may not be submitted when editing your own account (prevents
 * self-escalation and self-lockout).
 */
class UpdateUserRequest extends UserRequest
{
    /** Policy: superadmin targets only for superadmins, etc. */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->target());
    }

    /** The account from the route. */
    protected function target(): ?User
    {
        $user = $this->route('user');

        return $user instanceof User ? $user : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $target = $this->target();
        $self = $target && $this->user()->is($target);

        return $this->sharedRules() + [
            'email' => ['required', 'string', 'email:rfc', 'max:190', Rule::unique('users', 'email')->ignore($target?->id)],
            'roles' => $self ? ['prohibited'] : ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'distinct', Rule::in($this->assignableRoles())],
            'password' => ['nullable', 'string', 'confirmed', $this->passwordRule()],
        ];
    }
}
