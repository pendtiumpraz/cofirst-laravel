<?php

namespace App\Services\Academic;

use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Periodic progress reports (table `reports`): access rules, form options and the
 * shared list filters.
 */
class ProgressReportService
{
    public function __construct(private readonly AcademicScope $scope)
    {
    }

    /** Whether the user may read the report. */
    public function canView(User $user, Report $report): bool
    {
        return match (true) {
            $this->scope->isAdmin($user) => true,
            $user->hasRole('teacher') && ((int) $report->teacher_id === (int) $user->id || $this->scope->teacherTeaches($user, (int) $report->class_id)) => true,
            $user->hasRole('student') && (int) $report->student_id === (int) $user->id => true,
            $user->hasRole('parent') => AcademicSettings::reportsVisibleToParents() && $this->scope->isParentOf($user, (int) $report->student_id),
            default => false,
        };
    }

    /** Only the author may edit or delete a progress report. */
    public function canManage(User $user, Report $report): bool
    {
        return $user->hasRole('teacher') && (int) $report->teacher_id === (int) $user->id;
    }

    /**
     * Classes and their active students for the report form.
     *
     * Returns:
     *     Collection<int, array{id: int, name: string, course: ?string, students: array<int, array{id: int, name: string}>}>
     */
    public function formOptions(User $teacher): Collection
    {
        $classes = ClassName::query()
            ->whereIn('id', $this->scope->teacherClassIds($teacher))
            ->with('course:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'course_id']);

        // Reason: final reports are often written right after a class ends, so
        // students whose enrollment is completed stay selectable.
        $students = Enrollment::query()
            ->whereIn('status', AcademicScope::ACCESS_ENROLLMENT_STATUSES)
            ->whereIn('class_id', $classes->pluck('id'))
            ->with('student:id,name')
            ->get(['id', 'class_id', 'student_id'])
            ->filter(fn ($e) => $e->student)
            ->groupBy('class_id');

        return $classes->map(fn (ClassName $class) => [
            'id' => $class->id,
            'name' => $class->name,
            'course' => $class->course?->name,
            'students' => $students->get($class->id, collect())
                ->sortBy(fn ($e) => $e->student->name)
                ->map(fn ($e) => ['id' => $e->student->id, 'name' => $e->student->name])
                ->values()->all(),
        ])->values();
    }

    /**
     * Apply the list filters (class, type, student search, date range).
     */
    public function applyFilters(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->integer('class_id'), fn ($q, $id) => $q->where('reports.class_id', $id))
            ->when($request->integer('teacher_id'), fn ($q, $id) => $q->where('reports.teacher_id', $id))
            ->when(in_array($request->query('type'), array_keys(Report::types()), true), fn ($q) => $q->where('reports.type', $request->query('type')))
            ->when(is_string($request->query('search')) && trim($request->query('search')) !== '', function ($q) use ($request) {
                $term = '%'.trim($request->query('search')).'%';
                $q->where(fn ($w) => $w->where('reports.title', 'like', $term)
                    ->orWhereHas('student', fn ($s) => $s->where('name', 'like', $term)));
            })
            ->when($this->validDate($request->query('from')), fn ($q, $d) => $q->whereDate('reports.report_date', '>=', $d))
            ->when($this->validDate($request->query('to')), fn ($q, $d) => $q->whereDate('reports.report_date', '<=', $d));
    }

    /** Badge tone per report type. */
    public static function typeTone(?string $type): string
    {
        return match ($type) {
            Report::TYPE_FINAL => 'success',
            Report::TYPE_MONTHLY => 'info',
            Report::TYPE_INCIDENT => 'warning',
            default => 'neutral',
        };
    }

    /** Progress bar tone by percentage. */
    public static function progressTone(float|int|string|null $percent): string
    {
        $value = (float) $percent;

        return match (true) {
            $value >= 80 => 'success',
            $value >= 50 => 'brand',
            default => 'warning',
        };
    }

    /** Return the date string when it is a valid Y-m-d date. */
    private function validDate(mixed $value): ?string
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && strtotime($value) ? $value : null;
    }
}
