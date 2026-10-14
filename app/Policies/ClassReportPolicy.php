<?php

namespace App\Policies;

use App\Models\ClassReport;
use App\Models\User;
use App\Services\Academic\AcademicScope;
use App\Services\Academic\AcademicSettings;
use App\Services\Academic\ClassReportService;

/**
 * Meeting reports (berita acara).
 *
 * - teacher: writes reports for their classes; edits/deletes their own within
 *   academic.class_report_edit_days; reads their own, or every report of their
 *   classes when academic.class_report_visibility = class;
 * - admin/superadmin: read and delete everything; correct reports only when
 *   academic.admin_can_edit_class_reports is on;
 * - student: reads their own reports (UU PDP right of access);
 * - parent: reads their children's reports when academic.reports_visible_to_parents.
 */
class ClassReportPolicy
{
    public function __construct(
        private readonly AcademicScope $scope,
        private readonly ClassReportService $reports,
    ) {
    }

    /** Open the shared class-report index. */
    public function viewAny(User $user): bool
    {
        return $this->scope->isAdmin($user) || $user->hasRole('teacher');
    }

    /** Read one report. */
    public function view(User $user, ClassReport $classReport): bool
    {
        if ($this->scope->isAdmin($user)) {
            return true;
        }

        if ($user->hasRole('teacher')) {
            if ((int) $classReport->teacher_id === (int) $user->id) {
                return true;
            }

            if (AcademicSettings::classReportsSharedWithClassTeachers() && $this->scope->teacherTeaches($user, (int) $classReport->class_id)) {
                return true;
            }
        }

        if ($user->hasRole('student') && (int) $classReport->student_id === (int) $user->id) {
            return true;
        }

        return $user->hasRole('parent')
            && AcademicSettings::reportsVisibleToParents()
            && $this->scope->isParentOf($user, (int) $classReport->student_id);
    }

    /** Write new reports. */
    public function create(User $user): bool
    {
        return $user->hasRole('teacher');
    }

    /** Edit a report. */
    public function update(User $user, ClassReport $classReport): bool
    {
        if ($user->hasRole('teacher') && (int) $classReport->teacher_id === (int) $user->id) {
            return $this->reports->isEditable($classReport);
        }

        return $this->scope->isAdmin($user) && AcademicSettings::adminCanEditClassReports();
    }

    /** Delete a report. */
    public function delete(User $user, ClassReport $classReport): bool
    {
        if ($this->scope->isAdmin($user)) {
            return true;
        }

        return $user->hasRole('teacher')
            && (int) $classReport->teacher_id === (int) $user->id
            && $this->reports->isEditable($classReport);
    }

    /** Restore (not used: reports are hard-deleted with progress reverted). */
    public function restore(User $user, ClassReport $classReport): bool
    {
        return $this->scope->isAdmin($user);
    }

    /** Permanently delete. */
    public function forceDelete(User $user, ClassReport $classReport): bool
    {
        return $user->hasRole('superadmin');
    }
}
