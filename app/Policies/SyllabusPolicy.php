<?php

namespace App\Policies;

use App\Models\Syllabus;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SyllabusPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any syllabuses.
     */
    public function viewAny(User $user)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can view the syllabus.
     */
    public function view(User $user, Syllabus $syllabus)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can create syllabuses.
     */
    public function create(User $user)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can update the syllabus.
     */
    public function update(User $user, Syllabus $syllabus)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can delete the syllabus.
     */
    public function delete(User $user, Syllabus $syllabus)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can restore the syllabus.
     */
    public function restore(User $user, Syllabus $syllabus)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can permanently delete the syllabus.
     */
    public function forceDelete(User $user, Syllabus $syllabus)
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }
}