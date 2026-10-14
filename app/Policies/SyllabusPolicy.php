<?php

namespace App\Policies;

use App\Models\Syllabus;
use App\Models\User;
use App\Services\Academic\AcademicScope;

/**
 * Syllabus (meeting) access: admins manage; teachers read the meetings of the
 * curricula their classes use (active meetings only).
 */
class SyllabusPolicy
{
    public function __construct(private readonly AcademicScope $scope)
    {
    }

    /** Admin syllabus list. */
    public function viewAny(User $user): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Read one meeting. */
    public function view(User $user, Syllabus $syllabus): bool
    {
        if ($this->scope->isAdmin($user)) {
            return true;
        }

        return $user->hasRole('teacher')
            && $syllabus->status === 'active'
            && $this->scope->teacherCurriculumIds($user)->contains((int) $syllabus->curriculum_id);
    }

    /** Create meetings. */
    public function create(User $user): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Edit a meeting. */
    public function update(User $user, Syllabus $syllabus): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Archive a meeting. */
    public function delete(User $user, Syllabus $syllabus): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Restore an archived meeting. */
    public function restore(User $user, Syllabus $syllabus): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Permanently delete (superadmin only). */
    public function forceDelete(User $user, Syllabus $syllabus): bool
    {
        return $user->hasRole('superadmin');
    }
}
