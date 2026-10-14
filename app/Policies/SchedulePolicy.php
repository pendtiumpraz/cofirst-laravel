<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;
use App\Policies\Concerns\ChecksStaffPermissions;

/**
 * Weekly schedule management (Admin › Jadwal).
 */
class SchedulePolicy
{
    use ChecksStaffPermissions;

    /** Schedule calendar and list. */
    public function viewAny(User $actor): bool
    {
        return $this->staffCan($actor, 'view schedules', 'manage all schedules');
    }

    /** Slot detail. */
    public function view(User $actor, Schedule $schedule): bool
    {
        return $this->staffCan($actor, 'view schedules', 'manage all schedules');
    }

    /** Create a slot. */
    public function create(User $actor): bool
    {
        return $this->staffCan($actor, 'create schedules', 'manage all schedules');
    }

    /** Edit or (de)activate a slot. */
    public function update(User $actor, Schedule $schedule): bool
    {
        return $this->staffCan($actor, 'edit schedules', 'manage all schedules');
    }

    /** Delete a slot. */
    public function delete(User $actor, Schedule $schedule): bool
    {
        return $this->staffCan($actor, 'delete schedules', 'manage all schedules');
    }
}
