<?php

namespace App\Services\Teacher;

use App\Models\Attendance;
use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\PointTransaction;
use App\Models\Schedule;
use App\Models\User;
use App\Services\GamificationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Records the attendance of one class meeting (class + date) and awards the
 * gamification attendance points once per attended meeting.
 */
class AttendanceRecorder
{
    /** Teachers may record or correct meetings up to this many days back. */
    public const EDIT_WINDOW_DAYS = 14;

    /**
     * Tones of the design-system badges per status.
     *
     * Returns:
     *     array<string, string>: status => badge tone.
     */
    public static function tones(): array
    {
        return [
            Attendance::STATUS_PRESENT => 'success',
            Attendance::STATUS_LATE => 'warning',
            Attendance::STATUS_EXCUSED => 'info',
            Attendance::STATUS_ABSENT => 'danger',
        ];
    }

    /**
     * Students to mark for a meeting: active enrollments plus anyone already
     * recorded on that date (so a correction never hides a row).
     *
     * Args:
     *     class (ClassName): The class.
     *     date (CarbonImmutable): Meeting date.
     *
     * Returns:
     *     Collection<int, array{student: User, enrollment: Enrollment, attendance: ?Attendance, rate: ?float}>
     */
    public function roster(ClassName $class, CarbonImmutable $date): Collection
    {
        $existing = Attendance::query()
            ->where('class_id', $class->id)
            ->whereDate('attendance_date', $date->toDateString())
            ->get()
            ->keyBy('student_id');

        $enrollments = $class->enrollments()
            ->where(fn ($q) => $q->where('status', Enrollment::STATUS_ACTIVE)->orWhereIn('student_id', $existing->keys()->all()))
            ->with('student:id,name,profile_photo_path')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (Enrollment $e) => $e->student !== null)
            ->unique('student_id');

        $rates = Attendance::query()
            ->selectRaw('student_id, count(*) as total, sum(case when status in (?, ?) then 1 else 0 end) as attended', Attendance::ATTENDED_STATUSES)
            ->where('class_id', $class->id)
            ->whereIn('student_id', $enrollments->pluck('student_id')->all())
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        return $enrollments
            ->map(fn (Enrollment $e) => [
                'student' => $e->student,
                'enrollment' => $e,
                'attendance' => $existing->get($e->student_id),
                'rate' => ($row = $rates->get($e->student_id)) && $row->total > 0
                    ? round($row->attended / $row->total * 100, 1)
                    : null,
            ])
            ->sortBy(fn (array $row) => mb_strtolower($row['student']->name))
            ->values();
    }

    /**
     * Create or update the attendance rows of one meeting.
     *
     * Args:
     *     class (ClassName): The class.
     *     date (CarbonImmutable): Meeting date (already validated: not in the future).
     *     entries (array<int|string, array{status: string, notes?: ?string, minutes_late?: ?int}>): Keyed by student id.
     *     teacher (User): The teacher recording the meeting.
     *
     * Returns:
     *     array{saved: int, awarded: int}: Rows written and students who received points.
     */
    public function record(ClassName $class, CarbonImmutable $date, array $entries, User $teacher): array
    {
        $day = $date->toDateString();
        $scheduleByStudent = $this->schedulesByStudent($class, $date);
        $fallbackSchedule = $scheduleByStudent->first();
        $checkIn = $day === TeachingSchedule::today()->toDateString() ? TeachingSchedule::now()->format('H:i:s') : null;
        $newlyAttended = [];

        DB::transaction(function () use ($class, $day, $entries, $teacher, $scheduleByStudent, $fallbackSchedule, $checkIn, &$newlyAttended) {
            $existing = Attendance::query()
                ->where('class_id', $class->id)
                ->whereDate('attendance_date', $day)
                ->lockForUpdate()
                ->get()
                ->keyBy('student_id');

            $meeting = $existing->pluck('meeting_number')->filter()->first() ?? $this->nextMeetingNumber($class, $day);

            foreach ($entries as $studentId => $entry) {
                $studentId = (int) $studentId;
                $row = $existing->get($studentId)
                    ?? new Attendance(['class_id' => $class->id, 'student_id' => $studentId, 'attendance_date' => $day]);
                $wasAttended = $row->exists && $row->isAttended();
                $status = $entry['status'];
                $attended = in_array($status, Attendance::ATTENDED_STATUSES, true);
                $notes = trim((string) ($entry['notes'] ?? ''));

                $row->fill([
                    'status' => $status,
                    'notes' => $notes === '' ? null : $notes,
                    'minutes_late' => $status === Attendance::STATUS_LATE && isset($entry['minutes_late']) && $entry['minutes_late'] !== ''
                        ? (int) $entry['minutes_late']
                        : null,
                    'check_in_time' => $attended ? ($row->getRawOriginal('check_in_time') ?? $checkIn) : null,
                    'schedule_id' => $row->schedule_id ?? ($scheduleByStudent->get($studentId) ?? $fallbackSchedule),
                    'teacher_id' => $teacher->id,
                    'meeting_number' => $row->meeting_number ?? $meeting,
                ]);
                $row->save();

                if (! $wasAttended && $attended) {
                    $newlyAttended[] = $studentId;
                }
            }
        });

        return ['saved' => count($entries), 'awarded' => $this->awardPoints($class, $newlyAttended)];
    }

    /**
     * Map student id => schedule id of the class's slot on the date's weekday.
     *
     * Returns:
     *     Collection<int, int>
     */
    private function schedulesByStudent(ClassName $class, CarbonImmutable $date): Collection
    {
        return Schedule::query()
            ->where('schedules.class_id', $class->id)
            ->where('schedules.day_of_week', $date->dayOfWeekIso)
            ->leftJoin('enrollments', 'enrollments.id', '=', 'schedules.enrollment_id')
            ->orderBy('schedules.id')
            ->get(['schedules.id', 'enrollments.student_id'])
            ->mapWithKeys(fn ($row) => [(int) ($row->student_id ?? 0) => (int) $row->id]);
    }

    /** Meeting number for a new date: distinct earlier meeting dates + 1. */
    private function nextMeetingNumber(ClassName $class, string $day): int
    {
        $before = (int) Attendance::query()
            ->where('class_id', $class->id)
            ->whereRaw('date(attendance_date) < ?', [$day])
            ->selectRaw('count(distinct date(attendance_date)) as meetings')
            ->value('meetings');

        return $before + 1;
    }

    /**
     * Award attendance points through GamificationService for newly attended students.
     *
     * Returns:
     *     int: Number of students who received points.
     */
    private function awardPoints(ClassName $class, array $studentIds): int
    {
        if ($studentIds === []
            || ! setting('gamification.enabled', true)
            || ! method_exists(GamificationService::class, 'awardAttendancePoints')) {
            return 0;
        }

        $attended = Attendance::query()->attended()
            ->where('class_id', $class->id)
            ->whereIn('student_id', $studentIds)
            ->selectRaw('student_id, count(*) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id');

        $paid = PointTransaction::query()
            ->where('reason', PointTransaction::REASON_ATTENDANCE)
            ->where('related_type', $class->getMorphClass())
            ->where('related_id', $class->id)
            ->whereIn('user_id', $studentIds)
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $service = app(GamificationService::class);
        $awarded = 0;

        foreach (User::whereIn('id', $studentIds)->get() as $student) {
            // Reason: at most one award per attended meeting, so re-saving a sheet or
            // toggling absent -> present -> absent -> present never pays twice.
            if ((int) ($attended[$student->id] ?? 0) <= (int) ($paid[$student->id] ?? 0)) {
                continue;
            }

            try {
                $service->awardAttendancePoints($student, $class);
                $awarded++;
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $awarded;
    }
}
