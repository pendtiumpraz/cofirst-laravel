<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\Admin\AccessControl;
use Closure;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/**
 * Shared rules for creating and editing accounts in Admin › Pengguna.
 *
 * Roles are restricted to what the signed-in staff member may grant (only a
 * superadmin may grant superadmin/admin). Family links accept only students
 * (as children) and parents (as parents), and new links require the admin to
 * confirm the guardianship was verified (UU PDP Pasal 25: children's data is
 * processed with the consent of a parent/guardian).
 */
abstract class UserRequest extends AdminRequest
{
    /** The account being edited (null when creating). */
    abstract protected function target(): ?User;

    /**
     * Prepare booleans from toggles and normalise the email.
     */
    protected function prepareForValidation(): void
    {
        $merge = [
            'send_invite' => $this->boolean('send_invite'),
            'remove_photo' => $this->boolean('remove_photo'),
            'email' => is_string($this->input('email')) ? mb_strtolower(trim($this->input('email'))) : $this->input('email'),
        ];

        // Reason: a missing toggle must not silently deactivate an account (the
        // form omits it when you edit yourself).
        if ($this->has('is_active')) {
            $merge['is_active'] = $this->boolean('is_active');
        }

        $this->merge($merge);
    }

    /**
     * Rules common to create and update.
     *
     * @return array<string, mixed>
     */
    protected function sharedRules(): array
    {
        $selfId = $this->target()?->id ?? 0;

        return [
            'name' => ['required', 'string', 'max:120'],
            'is_active' => ['boolean'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
            'remove_photo' => ['boolean'],

            'full_name' => ['nullable', 'string', 'max:150'],
            'phone_number' => ['nullable', 'string', 'regex:/^[0-9+()\-\s]{8,20}$/'],
            'birth_date' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'address' => ['nullable', 'string', 'max:500'],
            'bio' => ['nullable', 'string', 'max:1000'],

            'children' => ['nullable', 'array', 'max:10'],
            'children.*' => ['integer', 'distinct', Rule::notIn([$selfId]), $this->holdsRole('student', 'Hanya akun siswa yang dapat ditautkan sebagai anak.')],
            'parents' => ['nullable', 'array', 'max:4'],
            'parents.*' => ['integer', 'distinct', Rule::notIn([$selfId]), $this->holdsRole('parent', 'Hanya akun orang tua yang dapat ditautkan sebagai orang tua/wali.')],
            // Reason: "accepted" is an implicit rule (it fails on a missing field), so it is
            // only applied when the submission really adds a family link.
            'guardian_verified' => $this->addsFamilyLinks() ? ['accepted'] : ['nullable'],
        ];
    }

    /**
     * Role names the signed-in user may assign.
     *
     * @return array<int, string>
     */
    protected function assignableRoles(): array
    {
        return AccessControl::assignableRoles($this->user(), Role::where('guard_name', 'web')->pluck('name')->all());
    }

    /**
     * Whether the submission adds at least one new parent–child link.
     */
    protected function addsFamilyLinks(): bool
    {
        $target = $this->target();
        $children = array_map('intval', (array) $this->input('children', []));
        $parents = array_map('intval', (array) $this->input('parents', []));

        if ($target) {
            $children = array_diff($children, $target->children()->pluck('users.id')->all());
            $parents = array_diff($parents, $target->parents()->pluck('users.id')->all());
        }

        $roles = (array) $this->input('roles', $target?->getRoleNames()->all() ?? []);
        $children = in_array('parent', $roles, true) ? $children : [];
        $parents = in_array('student', $roles, true) ? $parents : [];

        return $children !== [] || $parents !== [];
    }

    /**
     * Rule: the user id must hold the given role.
     */
    protected function holdsRole(string $role, string $message): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($role, $message) {
            $ok = User::whereKey((int) $value)->whereHas('roles', fn ($q) => $q->where('name', $role))->exists();
            if (! $ok) {
                $fail($message);
            }
        };
    }

    /**
     * Validated data without request-only keys; family keys are kept only for
     * the roles that use them.
     *
     * @return array<string, mixed>
     */
    public function accountData(): array
    {
        $data = $this->safe()->except(['photo', 'remove_photo', 'guardian_verified', 'password_confirmation', 'send_invite']);
        $data['send_invite'] = $this->boolean('send_invite');

        // Reason: you can never deactivate yourself, whatever is submitted.
        if ($this->target() && $this->user()->is($this->target())) {
            unset($data['is_active']);
        }

        $roles = $data['roles'] ?? $this->target()?->getRoleNames()->all() ?? [];

        // Reason: only sync links when the family section was really submitted
        // (its marker is enabled by Alpine); otherwise a broken script would wipe them.
        if (! $this->boolean('family_form')) {
            unset($data['children'], $data['parents']);

            return $data;
        }

        if (in_array('parent', $roles, true)) {
            $data['children'] = array_map('intval', (array) ($data['children'] ?? []));
        } else {
            unset($data['children']);
        }
        if (in_array('student', $roles, true)) {
            $data['parents'] = array_map('intval', (array) ($data['parents'] ?? []));
        } else {
            unset($data['parents']);
        }

        return $data;
    }

    /**
     * Indonesian attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'password' => 'kata sandi',
            'roles' => 'peran',
            'roles.*' => 'peran',
            'is_active' => 'status aktif',
            'send_invite' => 'kirim undangan',
            'photo' => 'foto profil',
            'full_name' => 'nama lengkap',
            'phone_number' => 'nomor telepon',
            'birth_date' => 'tanggal lahir',
            'gender' => 'jenis kelamin',
            'address' => 'alamat',
            'bio' => 'bio',
            'children' => 'anak',
            'children.*' => 'anak',
            'parents' => 'orang tua/wali',
            'parents.*' => 'orang tua/wali',
            'guardian_verified' => 'konfirmasi verifikasi orang tua/wali',
        ];
    }

    /**
     * Indonesian messages for the rules that matter most.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'roles.required' => 'Pilih minimal satu peran.',
            'roles.*.in' => 'Anda tidak dapat memberikan peran tersebut.',
            'roles.prohibited' => 'Anda tidak dapat mengubah peran akun Anda sendiri.',
            'email.unique' => 'Email ini sudah dipakai akun lain (termasuk akun yang diarsipkan).',
            'password.required_unless' => 'Isi kata sandi, atau pilih "Kirim tautan atur kata sandi".',
            'phone_number.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, +, - atau tanda kurung (8–20 karakter).',
            'guardian_verified.required' => 'Konfirmasi bahwa hubungan orang tua/wali sudah diverifikasi sebelum menautkan.',
            'guardian_verified.accepted' => 'Konfirmasi bahwa hubungan orang tua/wali sudah diverifikasi sebelum menautkan.',
            'children.*.not_in' => 'Akun tidak dapat ditautkan dengan dirinya sendiri.',
            'parents.*.not_in' => 'Akun tidak dapat ditautkan dengan dirinya sendiri.',
            'photo.dimensions' => 'Ukuran foto maksimal 4000×4000 piksel.',
        ];
    }
}
