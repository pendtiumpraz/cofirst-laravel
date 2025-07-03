<?php

namespace App\Policies;

use App\Models\ClassReport;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClassReportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'superadmin', 'teacher']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClassReport $classReport): bool
    {
        // Admin and superadmin can view all reports
        if ($user->hasAnyRole(['admin', 'superadmin'])) {
            return true;
        }

        // Teachers can only view their own reports
        if ($user->hasRole('teacher')) {
            return $classReport->teacher_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('teacher');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClassReport $classReport): bool
    {
        // Only teachers can update their own reports
        if (!$user->hasRole('teacher') || $classReport->teacher_id !== $user->id) {
            return false;
        }

        // Check if within grace period (1 week after class date)
        $daysSinceClass = now()->diffInDays($classReport->report_date);
        return $daysSinceClass <= 7;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClassReport $classReport): bool
    {
        // Admin and superadmin can delete any report
        if ($user->hasAnyRole(['admin', 'superadmin'])) {
            return true;
        }

        // Teachers can delete their own reports within grace period
        if ($user->hasRole('teacher') && $classReport->teacher_id === $user->id) {
            $daysSinceClass = now()->diffInDays($classReport->report_date);
            return $daysSinceClass <= 7;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ClassReport $classReport): bool
    {
        return $user->hasAnyRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ClassReport $classReport): bool
    {
        return $user->hasRole('superadmin');
    }
}