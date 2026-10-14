<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use App\Policies\Concerns\ChecksStaffPermissions;

/**
 * Course catalogue management (Admin › Kursus).
 */
class CoursePolicy
{
    use ChecksStaffPermissions;

    /** Course list. */
    public function viewAny(User $actor): bool
    {
        return $this->staffCan($actor, 'view courses');
    }

    /** Course detail. */
    public function view(User $actor, Course $course): bool
    {
        return $this->staffCan($actor, 'view courses');
    }

    /** Create a course. */
    public function create(User $actor): bool
    {
        return $this->staffCan($actor, 'create courses');
    }

    /** Edit or (de)activate a course. */
    public function update(User $actor, Course $course): bool
    {
        return $this->staffCan($actor, 'edit courses');
    }

    /** Archive a course (soft delete). */
    public function delete(User $actor, Course $course): bool
    {
        return $this->staffCan($actor, 'delete courses');
    }
}
