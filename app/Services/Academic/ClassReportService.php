<?php

namespace App\Services\Academic;

use App\Models\ClassName;
use App\Models\ClassReport;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Meeting reports (berita acara): who may see them, the edit window, the list of
 * meetings a teacher still has to report, and the writes (which also keep
 * class_progress up to date).
 */
class ClassReportService
{
    /** Fields a teacher writes per student. */
    public const STUDENT_FIELDS = [
        'remember_understanding', 'understand_comprehension', 'apply_application',
        'analyze_analysis', 'evaluate_evaluation', 'create_creation',
        'notes_recommendations', 'follow_up_notes',
    ];

    /** Fields shared by every student of one meeting. */
    public const SESSION_FIELDS = [
        'report_date', 'start_time', 'end_time', 'meeting_number', 'syllabus_id', 'material_id',
        'learning_concept', 'learning_media_link',
    ];

    public function __construct(
        private readonly ProgressService $progress,
        private readonly AcademicScope $scope,
    ) {
    }

    /** Bloom's taxonomy fields with Indonesian labels and hints. */
    public static function bloomFields(): array
    {
        return [
            'remember_understanding' => ['Mengingat', 'Istilah atau langkah yang sudah diingat.'],
            'understand_comprehension' => ['Memahami', 'Apa yang bisa dijelaskan dengan bahasa sendiri.'],
            'apply_application' => ['Menerapkan', 'Latihan yang berhasil dikerjakan.'],
            'analyze_analysis' => ['Menganalisis', 'Menemukan pola atau kesalahan program.'],
            'evaluate_evaluation' => ['Mengevaluasi', 'Membandingkan dan memilih solusi.'],
            'create_creation' => ['Mencipta', 'Karya atau variasi buatan sendiri.'],
        ];
    }

    /** Last moment a teacher may edit or delete the report. */
    public function editableUntil(ClassReport $report): Carbon
    {
        return Carbon::parse($report->report_date->toDateString())
            ->addDays(AcademicSettings::classReportEditDays())
            ->endOfDay();
    }

    /**
     * Whether the edit window is still open.
     *
     * Reason: the old check used now()->diffInDays($date), which is negative for past
     * dates in Carbon 3, so the 7-day limit was never enforced.
     */
    public function isEditable(ClassReport $report): bool
    {
        return $this->editableUntil($report)->isFuture();
    }

    /**
     * Class reports a user may read in the shared index, by active role.
     */
    public function visibleQuery(User $user, string $role): Builder
    {
        $query = ClassReport::query();

        return match (true) {
            in_array($role, ['admin', 'superadmin'], true) && $this->scope->isAdmin($user) => $query,
            $role === 'teacher' => $query->where(function (Builder $q) use ($user) {
                $q->where('class_reports.teacher_id', $user->id);

                if (AcademicSettings::classReportsSharedWithClassTeachers()) {
                    $q->orWhereIn('class_reports.class_id', $this->scope->teacherClassIds($user));
                }
            }),
            default => $query->whereRaw('1 = 0'),
        };
    }

    /**
     * Classes a teacher can write reports for (planned or active).
     *
     * Returns:
     *     Collection<int, ClassName>
     */
    public function teacherClasses(User $teacher): Collection
    {
        return ClassName::query()
            ->whereIn('id', $this->scope->teacherClassIds($teacher))
            ->whereIn('status', [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE])
            ->with(['course:id,name', 'curriculum:id,title'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Active students of a class (id and name only: data minimisation).
     *
     * Returns:
     *     Collection<int, User>
     */
    public function roster(ClassName $class): Collection
    {
        return User::query()
            ->whereIn('id', $class->activeEnrollments()->select('student_id'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /** Suggested meeting number: the last reported meeting plus one. */
    public function nextMeetingNumber(ClassName $class): int
    {
        return (int) ClassReport::query()->where('class_id', $class->id)->max('meeting_number') + 1;
    }

    /**
     * Meetings held in the last days (by the weekly schedule) that have no report yet.
     *
     * Reason: schedules are weekly templates; the old code hid a schedule forever after
     * its first report, so meetings 2..n could not be reported.
     *
     * Returns:
     *     Collection<int, array{class: ClassName, date: Carbon, start: string, end: string, schedule_id: ?int}>
     */
    public function pendingSessions(User $teacher, ?int $days = null): Collection
    {
        return $this->pendingSessionsFor($this->scope->teacherClassIds($teacher), $days);
    }

    /**
     * Unreported meetings of the given classes (see pendingSessions).
     *
     * Returns:
     *     Collection<int, array{class: ClassName, date: Carbon, start: string, end: string, schedule_id: ?int}>
     */
    public function pendingSessionsFor(Collection $classIds, ?int $days = null): Collection
    {
        $days ??= max(7, AcademicSettings::classReportEditDays());
        $today = Carbon::today(Format::timezone());
        $from = $today->copy()->subDays($days);

        $classes = ClassName::query()
            ->whereIn('id', $classIds)
            ->where('is_active', true)
            ->whereIn('status', [ClassName::STATUS_PLANNED, ClassName::STATUS_ACTIVE])
            ->whereHas('activeEnrollments')
            ->with(['course:id,name', 'teachers:id,name'])
            ->get()
            ->keyBy('id');

        if ($classes->isEmpty()) {
            return collect();
        }

        $slots = Schedule::query()->active()
            ->whereIn('class_id', $classes->keys())
            ->orderBy('day_of_week')->orderBy('start_time')
            ->get(['id', 'class_id', 'day_of_week', 'start_time', 'end_time'])
            ->unique(fn (Schedule $s) => $s->class_id.'|'.$s->day_of_week.'|'.$s->start_time?->format('H:i'));

        $reported = ClassReport::query()
            ->whereIn('class_id', $classes->keys())
            ->where('report_date', '>=', $from->toDateString())
            ->get(['class_id', 'report_date'])
            ->map(fn ($r) => $r->class_id.'|'.$r->report_date->toDateString())
            ->flip();

        $pending = collect();

        for ($date = $today->copy(); $date->gte($from); $date->subDay()) {
            foreach ($slots->where('day_of_week', $date->dayOfWeekIso) as $slot) {
                $class = $classes->get($slot->class_id);
                $key = $slot->class_id.'|'.$date->toDateString();

                if (! $class || $reported->has($key) || $pending->has($key)
                    || ($class->start_date && $date->lt($class->start_date->copy()->startOfDay()))
                    || ($class->end_date && $date->gt($class->end_date->copy()->endOfDay()))) {
                    continue;
                }

                $pending->put($key, [
                    'class' => $class,
                    'date' => $date->copy(),
                    'start' => $slot->start_time?->format('H:i') ?? '',
                    'end' => $slot->end_time?->format('H:i') ?? '',
                    'schedule_id' => $slot->id,
                ]);
            }
        }

        return $pending->values();
    }

    /**
     * Create one report per student for a meeting, and complete their progress.
     *
     * Args:
     *     teacher (User): The author (never taken from the request).
     *     class (ClassName): The class (ownership already validated).
     *     session (array): Validated SESSION_FIELDS (+ schedule_id).
     *     students (array<int, array>): Validated per-student entries with student_id.
     *
     * Returns:
     *     Collection<int, ClassReport>
     */
    public function createSession(User $teacher, ClassName $class, array $session, array $students): Collection
    {
        return DB::transaction(function () use ($teacher, $class, $session, $students) {
            $created = collect();

            foreach ($students as $entry) {
                $report = new ClassReport;
                $report->forceFill(array_merge(
                    array_intersect_key($session, array_flip(array_merge(self::SESSION_FIELDS, ['schedule_id']))),
                    array_intersect_key($entry, array_flip(self::STUDENT_FIELDS)),
                    [
                        'teacher_id' => $teacher->id,
                        'class_id' => $class->id,
                        'student_id' => (int) $entry['student_id'],
                        'curriculum_id' => $class->curriculum_id,
                    ]
                ));
                $report->save();

                $this->progress->recordReport($report);
                $created->push($report);
            }

            return $created;
        });
    }

    /**
     * Update the editable fields of a report; moves the progress row when the
     * meeting number changes.
     */
    public function update(ClassReport $report, array $data): ClassReport
    {
        return DB::transaction(function () use ($report, $data) {
            $oldMeeting = (int) $report->meeting_number;

            $report->fill(array_intersect_key($data, array_flip(array_merge(self::SESSION_FIELDS, self::STUDENT_FIELDS))));
            $report->save();

            if ($oldMeeting !== (int) $report->meeting_number) {
                $this->progress->revertReport($report->student_id, $report->class_id, $oldMeeting, $report->id);
            }

            $this->progress->recordReport($report->fresh());

            return $report;
        });
    }

    /** Delete a report and undo the meeting completion it caused. */
    public function delete(ClassReport $report): void
    {
        DB::transaction(function () use ($report) {
            $keys = [(int) $report->student_id, (int) $report->class_id, (int) $report->meeting_number, (int) $report->id];
            $report->delete();
            $this->progress->revertReport(...$keys);
        });
    }

    /**
     * Other reports of the same meeting (same class, date and meeting number).
     *
     * Returns:
     *     Collection<int, ClassReport>
     */
    public function sessionSiblings(ClassReport $report, Builder $visible): Collection
    {
        return $visible
            ->where('class_reports.class_id', $report->class_id)
            ->whereDate('class_reports.report_date', $report->report_date->toDateString())
            ->where('class_reports.meeting_number', $report->meeting_number)
            ->whereKeyNot($report->id)
            ->with('student:id,name')
            ->orderBy('student_id')
            ->get(['id', 'student_id', 'class_id', 'report_date', 'meeting_number']);
    }

    /** Enrollment statuses whose students can be reported. */
    public static function reportableStatuses(): array
    {
        return [Enrollment::STATUS_ACTIVE];
    }
}
