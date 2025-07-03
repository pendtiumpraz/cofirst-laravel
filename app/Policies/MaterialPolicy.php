<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaterialPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any materials.
     */
    public function viewAny(User $user)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can view the material.
     */
    public function view(User $user, Material $material)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can create materials.
     */
    public function create(User $user)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can update the material.
     */
    public function update(User $user, Material $material)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can delete the material.
     */
    public function delete(User $user, Material $material)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can restore the material.
     */
    public function restore(User $user, Material $material)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can permanently delete the material.
     */
    public function forceDelete(User $user, Material $material)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }
}