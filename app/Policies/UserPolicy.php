<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Who may manage which account in Admin › Pengguna.
 *
 * Rules (audit: privilege escalation to superadmin):
 * - staff need the matching permission (superadmin always passes);
 * - only a superadmin may edit, deactivate, archive or restore a superadmin,
 *   or another admin; an admin may edit their own account but not its roles;
 * - nobody may deactivate or archive their own account;
 * - the last active superadmin can never be deactivated or archived.
 */
class UserPolicy
{
    /** Users list. */
    public function viewAny(User $actor): bool
    {
        return $this->allowed($actor, 'view users');
    }

    /** User detail. */
    public function view(User $actor, User $target): bool
    {
        return $this->allowed($actor, 'view users');
    }

    /** Create accounts. */
    public function create(User $actor): bool
    {
        return $this->allowed($actor, 'create users');
    }

    /** Edit account details, roles, profile and family links. */
    public function update(User $actor, User $target): Response
    {
        if ($denied = $this->guardPrivileged($actor, $target, allowSelf: true)) {
            return $denied;
        }

        return $this->allowed($actor, 'edit users')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki izin mengubah pengguna.');
    }

    /** Activate / deactivate. */
    public function toggleStatus(User $actor, User $target): Response
    {
        if ($actor->is($target)) {
            return Response::deny('Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }
        if ($denied = $this->guardPrivileged($actor, $target)) {
            return $denied;
        }
        if ($target->is_active && $this->isLastActiveSuperadmin($target)) {
            return Response::deny('Superadmin aktif terakhir tidak dapat dinonaktifkan.');
        }

        return $this->allowed($actor, 'activate deactivate users') || $this->allowed($actor, 'edit users')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki izin mengaktifkan atau menonaktifkan pengguna.');
    }

    /** Archive (soft delete). */
    public function delete(User $actor, User $target): Response
    {
        if ($actor->is($target)) {
            return Response::deny('Anda tidak dapat mengarsipkan akun Anda sendiri.');
        }
        if ($denied = $this->guardPrivileged($actor, $target)) {
            return $denied;
        }
        if ($this->isLastActiveSuperadmin($target)) {
            return Response::deny('Superadmin aktif terakhir tidak dapat diarsipkan.');
        }

        return $this->allowed($actor, 'delete users')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki izin mengarsipkan pengguna.');
    }

    /** Restore an archived account. */
    public function restore(User $actor, User $target): Response
    {
        if ($denied = $this->guardPrivileged($actor, $target)) {
            return $denied;
        }

        return $this->allowed($actor, 'delete users')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki izin memulihkan pengguna.');
    }

    /** Send a password set/reset link. */
    public function sendPasswordLink(User $actor, User $target): Response
    {
        return $this->update($actor, $target);
    }

    /**
     * Whether the actor may change the target's roles.
     *
     * Reason: changing your own roles is how an admin would escalate or lock
     * themselves out, so it is never allowed from this screen.
     */
    public function changeRoles(User $actor, User $target): bool
    {
        return ! $actor->is($target) && $this->update($actor, $target)->allowed();
    }

    /**
     * Superadmin/admin targets can only be managed by a superadmin.
     */
    private function guardPrivileged(User $actor, User $target, bool $allowSelf = false): ?Response
    {
        if ($actor->hasRole('superadmin')) {
            return null;
        }
        if ($allowSelf && $actor->is($target)) {
            return null;
        }
        if ($target->hasRole('superadmin')) {
            return Response::deny('Hanya superadmin yang dapat mengelola akun superadmin.');
        }
        if ($target->hasRole('admin')) {
            return Response::deny('Hanya superadmin yang dapat mengelola akun admin lain.');
        }

        return null;
    }

    /**
     * Whether the target is the only active superadmin left.
     */
    private function isLastActiveSuperadmin(User $target): bool
    {
        if (! $target->hasRole('superadmin')) {
            return false;
        }

        return User::role('superadmin')->where('is_active', true)->whereKeyNot($target->id)->doesntExist();
    }

    /**
     * Staff permission check (superadmin always passes; missing permissions deny).
     */
    private function allowed(User $actor, string $permission): bool
    {
        if ($actor->hasRole('superadmin')) {
            return true;
        }

        return $actor->hasRole('admin') && $actor->checkPermissionTo($permission);
    }
}
