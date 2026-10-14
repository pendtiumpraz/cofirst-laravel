<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;
use App\Policies\Concerns\ChecksStaffPermissions;

/**
 * Enrollment management. Outside the back-office a student may view only
 * their own enrollment and a parent only their children's.
 */
class EnrollmentPolicy
{
    use ChecksStaffPermissions;

    /** Enrollment list. */
    public function viewAny(User $actor): bool
    {
        return $this->staffCan($actor, 'view classes');
    }

    /** Enrollment detail. */
    public function view(User $actor, Enrollment $enrollment): bool
    {
        if ($this->staffCan($actor, 'view classes')) {
            return true;
        }

        if ((int) $enrollment->student_id === (int) $actor->id) {
            return true;
        }

        return $actor->hasRole('parent') && $actor->isParentOf((int) $enrollment->student_id);
    }

    /** Create an enrollment. */
    public function create(User $actor): bool
    {
        return $this->staffCan($actor, 'edit classes');
    }

    /** Edit, approve, suspend or move an enrollment. */
    public function update(User $actor, Enrollment $enrollment): bool
    {
        return $this->staffCan($actor, 'edit classes');
    }

    /** Delete an enrollment record (only when it has no payments/attendance). */
    public function delete(User $actor, Enrollment $enrollment): bool
    {
        return $this->staffCan($actor, 'delete classes', 'edit classes');
    }
}
