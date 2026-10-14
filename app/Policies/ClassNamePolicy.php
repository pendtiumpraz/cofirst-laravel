<?php

namespace App\Policies;

use App\Models\ClassName;
use App\Models\User;
use App\Policies\Concerns\ChecksStaffPermissions;

/**
 * Class (rombel) management. Teachers may view only the classes they teach.
 */
class ClassNamePolicy
{
    use ChecksStaffPermissions;

    /** Class list. */
    public function viewAny(User $actor): bool
    {
        return $this->staffCan($actor, 'view classes');
    }

    /** Class detail: staff, or a teacher of this class. */
    public function view(User $actor, ClassName $class): bool
    {
        return $this->staffCan($actor, 'view classes')
            || ($actor->hasRole('teacher') && $actor->teachesClass($class));
    }

    /** Create a class. */
    public function create(User $actor): bool
    {
        return $this->staffCan($actor, 'create classes');
    }

    /** Edit a class, its teachers and its roster. */
    public function update(User $actor, ClassName $class): bool
    {
        return $this->staffCan($actor, 'edit classes');
    }

    /** Add/remove students (roster). */
    public function manageRoster(User $actor, ClassName $class): bool
    {
        return $this->staffCan($actor, 'edit classes');
    }

    /** Archive a class (soft delete). */
    public function delete(User $actor, ClassName $class): bool
    {
        return $this->staffCan($actor, 'delete classes');
    }
}
