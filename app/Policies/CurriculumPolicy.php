<?php

namespace App\Policies;

use App\Models\Curriculum;
use App\Models\User;
use App\Services\Academic\AcademicScope;

/**
 * Curriculum access.
 *
 * - admin/superadmin: manage everything;
 * - teacher: read the curricula used by their classes;
 * - student: read the curricula of their classes (active or completed enrollment);
 * - parent: read the curricula of their children's classes.
 */
class CurriculumPolicy
{
    public function __construct(private readonly AcademicScope $scope)
    {
    }

    /** Admin curriculum list. */
    public function viewAny(User $user): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Read one curriculum. */
    public function view(User $user, Curriculum $curriculum): bool
    {
        if ($this->scope->isAdmin($user)) {
            return true;
        }

        if ($user->hasRole('teacher') && $this->scope->teacherCurriculumIds($user)->contains($curriculum->id)) {
            return true;
        }

        if ($user->hasRole('student') && $this->scope->studentClassIdsForCurriculum($user, $curriculum->id)->isNotEmpty()) {
            return true;
        }

        if ($user->hasRole('parent')) {
            foreach ($user->children()->get() as $child) {
                if ($this->scope->studentClassIdsForCurriculum($child, $curriculum->id)->isNotEmpty()) {
                    return true;
                }
            }
        }

        return false;
    }

    /** Create curricula. */
    public function create(User $user): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Edit a curriculum. */
    public function update(User $user, Curriculum $curriculum): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Archive a curriculum. */
    public function delete(User $user, Curriculum $curriculum): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Restore an archived curriculum. */
    public function restore(User $user, Curriculum $curriculum): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Permanently delete (superadmin only). */
    public function forceDelete(User $user, Curriculum $curriculum): bool
    {
        return $user->hasRole('superadmin');
    }
}
