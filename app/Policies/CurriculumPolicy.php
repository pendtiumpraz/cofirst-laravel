<?php

namespace App\Policies;

use App\Models\Curriculum;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CurriculumPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any curriculums.
     */
    public function viewAny(User $user)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can view the curriculum.
     */
    public function view(User $user, Curriculum $curriculum)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can create curriculums.
     */
    public function create(User $user)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can update the curriculum.
     */
    public function update(User $user, Curriculum $curriculum)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can delete the curriculum.
     */
    public function delete(User $user, Curriculum $curriculum)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can restore the curriculum.
     */
    public function restore(User $user, Curriculum $curriculum)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can permanently delete the curriculum.
     */
    public function forceDelete(User $user, Curriculum $curriculum)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }
}