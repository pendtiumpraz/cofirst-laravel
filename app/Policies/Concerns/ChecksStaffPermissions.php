<?php

namespace App\Policies\Concerns;

use App\Models\User;

/**
 * Shared permission check for the admin-core policies.
 *
 * Superadmins always pass. Admins need the named permission (RoleSeeder grants
 * the defaults; superadmins can change them in Peran & Izin). Missing permission
 * rows deny instead of throwing.
 */
trait ChecksStaffPermissions
{
    /**
     * Whether the staff member may perform the action.
     *
     * Args:
     *     actor (User): Signed-in user.
     *     permissions (string ...): Any one of these permissions is enough.
     */
    protected function staffCan(User $actor, string ...$permissions): bool
    {
        if ($actor->hasRole('superadmin')) {
            return true;
        }

        if (! $actor->hasRole('admin')) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($actor->checkPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }
}
