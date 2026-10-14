<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;
use App\Services\Academic\AcademicScope;
use App\Services\Academic\ProgressService;

/**
 * Material access.
 *
 * - admin/superadmin: everything (also inactive materials);
 * - teacher: active materials of active meetings of the curricula their classes use;
 * - student: active material, active meeting, an active/completed enrollment in a class
 *   with that curriculum, and the meeting unlocked (academic.material_unlock_mode);
 * - parent: never opens paid content directly (sees titles in the child's progress).
 */
class MaterialPolicy
{
    public function __construct(
        private readonly AcademicScope $scope,
        private readonly ProgressService $progress,
    ) {
    }

    /** Admin material list. */
    public function viewAny(User $user): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Open the material page. */
    public function view(User $user, Material $material): bool
    {
        if ($this->scope->isAdmin($user)) {
            return true;
        }

        if ($user->hasRole('teacher') && $this->teacherCanRead($user, $material)) {
            return true;
        }

        return $user->hasRole('student') && $this->progress->canStudentAccess($user, $material);
    }

    /** Download or stream the material file. */
    public function download(User $user, Material $material): bool
    {
        return filled($material->file_path) && $this->view($user, $material);
    }

    /** Create materials. */
    public function create(User $user): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Edit a material. */
    public function update(User $user, Material $material): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Archive a material. */
    public function delete(User $user, Material $material): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Restore an archived material. */
    public function restore(User $user, Material $material): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Permanently delete (superadmin only). */
    public function forceDelete(User $user, Material $material): bool
    {
        return $user->hasRole('superadmin');
    }

    /** Teachers read active materials of active meetings of their curricula. */
    private function teacherCanRead(User $teacher, Material $material): bool
    {
        $syllabus = $material->syllabus;

        return $syllabus
            && $material->status === 'active'
            && $syllabus->status === 'active'
            && $this->scope->teacherCurriculumIds($teacher)->contains((int) $syllabus->curriculum_id);
    }
}
