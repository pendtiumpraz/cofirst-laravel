<?php

namespace App\Services\Academic;

use App\Models\ClassName;
use App\Models\ClassProgress;
use App\Models\ClassReport;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\Syllabus;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Meeting progress (class_progress) of students: how far a student is in a class,
 * which materials are unlocked, and the writes that keep it up to date.
 *
 * class_progress is written when a teacher files a class report for a student
 * (meeting completed) or marks a meeting for the whole class. Without these writes
 * materials never unlocked and every progress bar showed 0%.
 */
class ProgressService
{
    public const STATUS_NOT_STARTED = 'not_started';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public function __construct(private readonly AcademicScope $scope)
    {
    }

    /**
     * Indonesian labels of the meeting statuses.
     *
     * Returns:
     *     array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_IN_PROGRESS => 'Sedang berjalan',
            self::STATUS_NOT_STARTED => 'Belum dimulai',
        ];
    }

    /**
     * Progress of one student in several classes, using two queries in total.
     *
     * Args:
     *     student (User): The student.
     *     classes (iterable<ClassName>): Classes (null entries are ignored).
     *     withMaterials (bool): Also load the active materials of every meeting.
     *
     * Returns:
     *     Collection<int, ProgressSummary>: keyed by class id.
     */
    public function summariesFor(User $student, iterable $classes, bool $withMaterials = false): Collection
    {
        $classes = collect($classes)->filter()->unique('id')->keyBy('id');

        if ($classes->isEmpty()) {
            return collect();
        }

        $syllabuses = $this->activeSyllabuses($classes->pluck('curriculum_id')->filter()->unique(), $withMaterials);

        $rows = ClassProgress::query()
            ->where('student_id', $student->id)
            ->whereIn('class_id', $classes->keys())
            ->get(['class_id', 'meeting_number', 'status', 'completed_at', 'notes'])
            ->groupBy('class_id');

        return $classes->map(fn (ClassName $class) => $this->build(
            $class,
            $class->curriculum_id ? $syllabuses->get($class->curriculum_id, collect()) : collect(),
            $rows->get($class->id, collect())
        ));
    }

    /** Progress of one student in one class. */
    public function summary(User $student, ClassName $class, bool $withMaterials = false): ProgressSummary
    {
        return $this->summariesFor($student, [$class], $withMaterials)->get($class->id);
    }

    /**
     * Whether a material belongs to an unlocked meeting.
     *
     * Reason: the UI groups materials by their syllabus meeting, so the material always
     * unlocks with its own meeting; meeting_start..meeting_end can extend the range.
     */
    public function isMaterialUnlocked(Material $material, ProgressSummary $summary, ?int $syllabusMeeting = null): bool
    {
        $syllabusMeeting ??= $material->syllabus?->meeting_number;
        $start = (int) ($material->meeting_start ?: $syllabusMeeting);
        $start = $syllabusMeeting ? min($start, (int) $syllabusMeeting) : $start;
        $end = max($start, (int) ($material->meeting_end ?: $start));

        foreach ($summary->unlockedMeetings as $number) {
            if ($number >= $start && $number <= $end) {
                return true;
            }
        }

        return false;
    }

    /**
     * IDs of the unlocked materials of a summary built with materials.
     *
     * Returns:
     *     array<int, int>
     */
    public function unlockedMaterialIds(ProgressSummary $summary): array
    {
        return $summary->meetings
            ->flatMap(fn ($meeting) => $meeting['syllabus']->relationLoaded('activeMaterials')
                ? $meeting['syllabus']->activeMaterials
                    ->filter(fn (Material $material) => $this->isMaterialUnlocked($material, $summary, $meeting['number']))
                    ->pluck('id')
                : [])
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Whether a student may open a material: active material and meeting, an
     * access-giving enrollment in a class with that curriculum, and an unlocked meeting.
     */
    public function canStudentAccess(User $student, Material $material): bool
    {
        $syllabus = $material->relationLoaded('syllabus') ? $material->syllabus : $material->syllabus()->first();

        if (! $syllabus || $syllabus->trashed() || $material->status !== 'active' || $syllabus->status !== 'active') {
            return false;
        }

        $classIds = $this->scope->studentClassIdsForCurriculum($student, (int) $syllabus->curriculum_id);

        if ($classIds->isEmpty()) {
            return false;
        }

        $classes = $this->scope->studentEnrollments($student)->only($classIds->all())->map->className;

        foreach ($this->summariesFor($student, $classes) as $summary) {
            if ($this->isMaterialUnlocked($material, $summary, $syllabus->meeting_number)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mark the report's meeting as completed for its student (called inside the
     * class report transaction).
     */
    public function recordReport(ClassReport $report): void
    {
        $syllabusId = $report->syllabus_id ?: $this->syllabusIdFor((int) $report->class_id, (int) $report->meeting_number);

        if (! $syllabusId) {
            return;
        }

        $completedAt = Carbon::parse($report->report_date->toDateString().' '.($report->end_time?->format('H:i') ?? '12:00'));

        ClassProgress::updateOrCreate(
            ['student_id' => $report->student_id, 'class_id' => $report->class_id, 'meeting_number' => $report->meeting_number],
            ['syllabus_id' => $syllabusId, 'material_id' => $report->material_id, 'status' => self::STATUS_COMPLETED, 'completed_at' => $completedAt]
        );
    }

    /**
     * Undo the completion of a meeting when its last class report disappears.
     *
     * Args:
     *     studentId (int), classId (int), meetingNumber (int): The progress row key.
     *     exceptReportId (int|null): A report to ignore (the one being deleted/moved).
     */
    public function revertReport(int $studentId, int $classId, int $meetingNumber, ?int $exceptReportId = null): void
    {
        $stillReported = ClassReport::query()
            ->where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('meeting_number', $meetingNumber)
            ->when($exceptReportId, fn ($q) => $q->whereKeyNot($exceptReportId))
            ->exists();

        if ($stillReported) {
            return;
        }

        ClassProgress::query()
            ->where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('meeting_number', $meetingNumber)
            ->where('status', self::STATUS_COMPLETED)
            ->update(['status' => self::STATUS_NOT_STARTED, 'completed_at' => null, 'updated_at' => now()]);
    }

    /**
     * Set the status of one meeting for every active student of a class.
     *
     * Returns:
     *     int: Number of students updated.
     */
    public function markMeeting(ClassName $class, Syllabus $syllabus, string $status): int
    {
        $studentIds = $class->activeEnrollments()->pluck('student_id');

        return DB::transaction(function () use ($class, $syllabus, $status, $studentIds) {
            $existing = ClassProgress::query()
                ->where('class_id', $class->id)
                ->where('meeting_number', $syllabus->meeting_number)
                ->whereIn('student_id', $studentIds)
                ->get()
                ->keyBy('student_id');

            foreach ($studentIds as $studentId) {
                $row = $existing->get($studentId);
                // Reason: keep the original completion date when a meeting is re-confirmed.
                $completedAt = $status === self::STATUS_COMPLETED ? ($row?->completed_at ?? now()) : null;

                ClassProgress::updateOrCreate(
                    ['student_id' => $studentId, 'class_id' => $class->id, 'meeting_number' => $syllabus->meeting_number],
                    ['syllabus_id' => $syllabus->id, 'status' => $status, 'completed_at' => $completedAt]
                );
            }

            return $studentIds->count();
        });
    }

    /**
     * Per-meeting overview of a class for its teacher.
     *
     * Returns:
     *     Collection<int, array{syllabus: Syllabus, completed: int, in_progress: int, students: int, reported: int, last_report: ?string}>
     */
    public function classOverview(ClassName $class): Collection
    {
        if (! $class->curriculum_id) {
            return collect();
        }

        $syllabuses = $this->activeSyllabuses(collect([$class->curriculum_id]), false)->get($class->curriculum_id, collect());
        $studentIds = $class->activeEnrollments()->pluck('student_id');

        $counts = ClassProgress::query()
            ->where('class_id', $class->id)
            ->whereIn('student_id', $studentIds)
            ->selectRaw('meeting_number, status, count(*) as total')
            ->groupBy('meeting_number', 'status')
            ->get()
            ->groupBy('meeting_number');

        $reports = ClassReport::query()
            ->where('class_id', $class->id)
            ->selectRaw('meeting_number, count(distinct student_id) as students, max(report_date) as last_date')
            ->groupBy('meeting_number')
            ->get()
            ->keyBy('meeting_number');

        return $syllabuses->map(function (Syllabus $syllabus) use ($counts, $reports, $studentIds) {
            $byStatus = $counts->get($syllabus->meeting_number, collect())->pluck('total', 'status');

            return [
                'syllabus' => $syllabus,
                'completed' => (int) ($byStatus[self::STATUS_COMPLETED] ?? 0),
                'in_progress' => (int) ($byStatus[self::STATUS_IN_PROGRESS] ?? 0),
                'students' => $studentIds->count(),
                'reported' => (int) ($reports->get($syllabus->meeting_number)?->students ?? 0),
                'last_report' => $reports->get($syllabus->meeting_number)?->last_date,
            ];
        })->values();
    }

    /**
     * Average completion of several classes (active students × active meetings).
     *
     * Returns:
     *     Collection<int, array{students: int, meetings: int, completed: int, percent: float}>: keyed by class id.
     */
    public function classCompletionRates(Collection $classes): Collection
    {
        $classes = $classes->filter()->keyBy('id');

        if ($classes->isEmpty()) {
            return collect();
        }

        $meetings = Syllabus::query()->active()
            ->whereIn('curriculum_id', $classes->pluck('curriculum_id')->filter()->unique())
            ->selectRaw('curriculum_id, count(*) as total')
            ->groupBy('curriculum_id')
            ->pluck('total', 'curriculum_id');

        $students = Enrollment::query()->active()
            ->whereIn('class_id', $classes->keys())
            ->selectRaw('class_id, count(*) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $completed = ClassProgress::query()
            ->where('status', self::STATUS_COMPLETED)
            ->whereIn('class_id', $classes->keys())
            ->whereExists(fn ($q) => $q->selectRaw('1')->from('enrollments')
                ->whereColumn('enrollments.class_id', 'class_progress.class_id')
                ->whereColumn('enrollments.student_id', 'class_progress.student_id')
                ->where('enrollments.status', Enrollment::STATUS_ACTIVE))
            ->selectRaw('class_id, count(*) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        return $classes->map(function (ClassName $class) use ($meetings, $students, $completed) {
            $meetingCount = (int) ($meetings[$class->curriculum_id] ?? 0);
            $studentCount = (int) ($students[$class->id] ?? 0);
            $done = (int) ($completed[$class->id] ?? 0);
            $possible = $meetingCount * $studentCount;

            return [
                'students' => $studentCount,
                'meetings' => $meetingCount,
                'completed' => $done,
                'percent' => $possible > 0 ? round(min(100, $done / $possible * 100), 1) : 0.0,
            ];
        });
    }

    /**
     * Rebuild completed meetings from the existing class reports (backfill).
     *
     * Returns:
     *     int: Number of reports processed.
     */
    public function rebuildFromReports(?int $classId = null): int
    {
        $count = 0;

        ClassReport::query()
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->orderBy('id')
            ->chunkById(200, function ($reports) use (&$count) {
                foreach ($reports as $report) {
                    $this->recordReport($report);
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Active syllabuses of several curricula, grouped by curriculum id.
     *
     * Returns:
     *     Collection<int, Collection<int, Syllabus>>
     */
    private function activeSyllabuses(Collection $curriculumIds, bool $withMaterials): Collection
    {
        if ($curriculumIds->isEmpty()) {
            return collect();
        }

        return Syllabus::query()->active()
            ->whereIn('curriculum_id', $curriculumIds)
            ->when($withMaterials, fn ($q) => $q->with(['activeMaterials']))
            ->orderBy('meeting_number')
            ->get()
            ->groupBy('curriculum_id');
    }

    /** Build the summary of one class. */
    private function build(ClassName $class, Collection $syllabuses, Collection $rows): ProgressSummary
    {
        $rows = $rows->keyBy('meeting_number');

        $meetings = $syllabuses->map(fn (Syllabus $syllabus) => [
            'syllabus' => $syllabus,
            'number' => (int) $syllabus->meeting_number,
            'status' => $rows->get($syllabus->meeting_number)?->status ?? self::STATUS_NOT_STARTED,
            'completed_at' => $rows->get($syllabus->meeting_number)?->completed_at,
            'notes' => $rows->get($syllabus->meeting_number)?->notes,
        ])->values();

        $unlocked = $this->unlockedMeetings($meetings);

        $meetings = $meetings->map(fn ($m) => $m + ['unlocked' => in_array($m['number'], $unlocked, true)]);

        return new ProgressSummary($class, $meetings, $unlocked);
    }

    /**
     * Meeting numbers whose materials are open, by setting academic.material_unlock_mode.
     *
     * Returns:
     *     array<int, int>
     */
    private function unlockedMeetings(Collection $meetings): array
    {
        $mode = AcademicSettings::unlockMode();

        if ($mode === AcademicSettings::UNLOCK_ALL) {
            return $meetings->pluck('number')->all();
        }

        $open = $meetings->where('status', self::STATUS_COMPLETED)->pluck('number');

        if ($mode === AcademicSettings::UNLOCK_UPCOMING) {
            $open = $open->merge($meetings->where('status', self::STATUS_IN_PROGRESS)->pluck('number'));
            $next = $meetings->first(fn ($m) => $m['status'] !== self::STATUS_COMPLETED);

            if ($next) {
                $open->push($next['number']);
            }
        }

        return $open->map(fn ($n) => (int) $n)->unique()->values()->all();
    }

    /** Syllabus id of a class's meeting, if the curriculum has one. */
    private function syllabusIdFor(int $classId, int $meetingNumber): ?int
    {
        $curriculumId = ClassName::query()->whereKey($classId)->value('curriculum_id');

        if (! $curriculumId) {
            return null;
        }

        return Syllabus::query()
            ->where('curriculum_id', $curriculumId)
            ->where('meeting_number', $meetingNumber)
            ->value('id');
    }
}
