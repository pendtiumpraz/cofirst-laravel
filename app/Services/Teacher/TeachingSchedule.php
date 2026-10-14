<?php

namespace App\Services\Teacher;

use App\Models\Attendance;
use App\Models\ClassName;
use App\Models\ClassProgress;
use App\Models\ClassReport;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Read model of a teacher's timetable: active classes, weekly slots, dated
 * sessions, record status (attendance / berita acara) and students that need
 * attention.
 *
 * Source of truth for "who teaches what" is the class_teacher pivot
 * (User::taughtClasses()), never schedules.teacher_assignment_id, so a teacher
 * removed from a class immediately stops seeing its sessions.
 * All "today" logic uses the school timezone (setting general.timezone).
 */
class TeachingSchedule
{
    /**
     * Current time in the school timezone, Indonesian locale.
     *
     * Returns:
     *     CarbonImmutable: Now in setting('general.timezone') (default Asia/Jakarta).
     */
    public static function now(): CarbonImmutable
    {
        $timezone = (string) (setting('general.timezone') ?: 'Asia/Jakarta');

        try {
            return CarbonImmutable::now($timezone)->locale('id');
        } catch (Throwable) {
            return CarbonImmutable::now('Asia/Jakarta')->locale('id');
        }
    }

    /** Start of today in the school timezone. */
    public static function today(): CarbonImmutable
    {
        return self::now()->startOfDay();
    }

    /**
     * Parse a Y-m-d string as a local date (start of day), or null when invalid.
     *
     * Args:
     *     value (string|null): Date string from the request.
     */
    public static function parseDate(?string $value): ?CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $value, self::today()->getTimezone());
        } catch (Throwable) {
            return null;
        }

        return $date && $date->format('Y-m-d') === $value ? $date->locale('id') : null;
    }

    /**
     * Format a date with Indonesian day and month names.
     *
     * Args:
     *     date (CarbonInterface|null): Date to format (only the calendar date is used).
     *     format (string): translatedFormat pattern, default "l, j M Y".
     *
     * Returns:
     *     string: e.g. "Senin, 13 Okt 2026"; "—" for null.
     */
    public static function format(?CarbonInterface $date, string $format = 'l, j M Y'): string
    {
        if (! $date) {
            return '—';
        }

        return CarbonImmutable::parse($date->format('Y-m-d'))->locale('id')->translatedFormat($format);
    }

    /**
     * Format a timestamp in the school timezone with Indonesian month names.
     *
     * Args:
     *     moment (CarbonInterface|null): A stored (UTC) timestamp.
     *     format (string): translatedFormat pattern, default "j M Y, H.i".
     *
     * Returns:
     *     string: e.g. "13 Okt 2026, 09.30"; "—" for null.
     */
    public static function formatDateTime(?CarbonInterface $moment, string $format = 'j M Y, H.i'): string
    {
        if (! $moment) {
            return '—';
        }

        return CarbonImmutable::instance($moment)->setTimezone(self::now()->getTimezone())->locale('id')->translatedFormat($format);
    }

    /**
     * Classes the teacher currently teaches (planned or active), with course and
     * the number of active students (active_students_count).
     *
     * Args:
     *     teacher (User): The teacher.
     *
     * Returns:
     *     EloquentCollection<int, ClassName>
     */
    public function activeClasses(User $teacher): EloquentCollection
    {
        return $teacher->taughtClasses()
            ->with('course:id,name')
            ->withCount(['enrollments as active_students_count' => fn ($q) => $q->where('status', Enrollment::STATUS_ACTIVE)])
            ->orderBy('class_names.name')
            ->get();
    }

    /**
     * Weekly slots of the given classes: one entry per (class, day, start, end).
     *
     * Args:
     *     classes (Collection<int, ClassName>): Classes with active_students_count loaded.
     *
     * Returns:
     *     Collection<int, array{class: ClassName, day: int, start: string, end: string, room: ?string, schedule_id: int, student_name: ?string}>
     */
    public function slots(Collection $classes): Collection
    {
        if ($classes->isEmpty()) {
            return collect();
        }

        $byId = $classes->keyBy('id');

        $rows = Schedule::query()
            ->whereIn('class_id', $byId->keys())
            ->where('schedules.is_active', true)
            ->with(['enrollment' => fn ($q) => $q->select('id', 'student_id', 'status')->with('student:id,name')])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return $rows
            ->groupBy(fn (Schedule $s) => implode('|', [$s->class_id, $s->day_of_week, $s->start_time?->format('H:i'), $s->end_time?->format('H:i')]))
            // Reason: a slot whose enrollments were all dropped/cancelled is no longer taught.
            ->filter(fn (Collection $group) => $group->contains(
                fn (Schedule $s) => $s->enrollment === null || in_array($s->enrollment->status, Enrollment::SEAT_STATUSES, true)
            ))
            ->map(function (Collection $group) use ($byId) {
                /** @var Schedule $first */
                $first = $group->first();
                $class = $byId->get($first->class_id);
                $active = $group->filter(fn (Schedule $s) => $s->enrollment?->status === Enrollment::STATUS_ACTIVE);

                return [
                    'class' => $class,
                    'day' => (int) $first->day_of_week,
                    'start' => $first->start_time?->format('H:i') ?? '00:00',
                    'end' => $first->end_time?->format('H:i') ?? '00:00',
                    'room' => $group->pluck('room')->filter()->first(),
                    'schedule_id' => (int) $first->id,
                    // Private (one student) classes show the student's name instead of a count.
                    'student_name' => (int) ($class->active_students_count ?? 0) === 1 && $active->count() === 1
                        ? $active->first()->enrollment->student?->name
                        : null,
                ];
            })
            ->sortBy(fn (array $slot) => $slot['day'].' '.$slot['start'])
            ->values();
    }

    /**
     * Expand weekly slots into dated sessions, skipping dates outside each class's
     * start_date..end_date.
     *
     * Args:
     *     slots (Collection): Output of slots().
     *     from (CarbonImmutable): First date (inclusive).
     *     to (CarbonImmutable): Last date (inclusive).
     *
     * Returns:
     *     Collection<int, TeachingSession>: Sorted by date and start time.
     */
    public function sessionsBetween(Collection $slots, CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        $sessions = collect();

        for ($day = $from->startOfDay(); $day->lte($to); $day = $day->addDay()) {
            $date = $day->toDateString();

            foreach ($slots as $slot) {
                /** @var ClassName $class */
                $class = $slot['class'];

                if ($slot['day'] !== $day->dayOfWeekIso
                    || ($class->start_date && $date < $class->start_date->format('Y-m-d'))
                    || ($class->end_date && $date > $class->end_date->format('Y-m-d'))) {
                    continue;
                }

                $sessions->push(new TeachingSession(
                    $class, $day, $slot['start'], $slot['end'], $slot['room'], $slot['schedule_id'], $slot['student_name']
                ));
            }
        }

        return $sessions->sortBy(fn (TeachingSession $s) => $s->date->toDateString().' '.$s->start)->values();
    }

    /**
     * Fill attendance and berita acara counters of the sessions (2 grouped queries).
     *
     * Args:
     *     sessions (Collection<int, TeachingSession>): Sessions to annotate.
     *
     * Returns:
     *     Collection<int, TeachingSession>: The same sessions.
     */
    public function withRecordStatus(Collection $sessions): Collection
    {
        if ($sessions->isEmpty()) {
            return $sessions;
        }

        $classIds = $sessions->map(fn (TeachingSession $s) => $s->class->id)->unique()->values()->all();
        $range = [$sessions->min(fn ($s) => $s->date->toDateString()), $sessions->max(fn ($s) => $s->date->toDateString())];

        // Reason: date() normalises SQLite rows stored as "Y-m-d" and "Y-m-d H:i:s" alike.
        $attendance = Attendance::query()
            ->selectRaw('class_id, date(attendance_date) as day, count(*) as total, sum(case when status in (?, ?) then 1 else 0 end) as attended', Attendance::ATTENDED_STATUSES)
            ->whereIn('class_id', $classIds)
            ->whereRaw('date(attendance_date) between ? and ?', $range)
            ->groupByRaw('class_id, date(attendance_date)')
            ->get()
            ->keyBy(fn ($row) => $row->class_id.':'.$row->day);

        $reports = ClassReport::query()
            ->selectRaw('class_id, date(report_date) as day, count(*) as total')
            ->whereIn('class_id', $classIds)
            ->whereRaw('date(report_date) between ? and ?', $range)
            ->groupByRaw('class_id, date(report_date)')
            ->get()
            ->keyBy(fn ($row) => $row->class_id.':'.$row->day);

        foreach ($sessions as $session) {
            $row = $attendance->get($session->key());
            $session->attendanceCount = (int) ($row->total ?? 0);
            $session->attendedCount = (int) ($row->attended ?? 0);
            $session->reportCount = (int) ($reports->get($session->key())->total ?? 0);
        }

        return $sessions;
    }

    /**
     * Past sessions of the last `$days` days (today included once ended) that
     * still need attendance or berita acara.
     *
     * Args:
     *     slots (Collection): Output of slots().
     *     days (int): Look-back window in days.
     *
     * Returns:
     *     Collection<int, TeachingSession>: Newest first.
     */
    public function pendingSessions(Collection $slots, int $days = 7): Collection
    {
        $now = self::now();
        $sessions = $this->sessionsBetween($slots, $now->startOfDay()->subDays($days), $now->startOfDay());

        return $this->withRecordStatus($sessions->filter(fn (TeachingSession $s) => $s->hasEnded($now))->values())
            ->filter(fn (TeachingSession $s) => ! $s->hasAttendance() || $s->missingReports() > 0)
            ->sortByDesc(fn (TeachingSession $s) => $s->date->toDateString().' '.$s->start)
            ->values();
    }

    /**
     * Students of running classes that need attention: two absences in a row,
     * attendance below 75% (at least 3 meetings) or two unfinished meetings.
     *
     * Args:
     *     classes (Collection<int, ClassName>): The teacher's classes.
     *     limit (int): Maximum number of rows.
     *
     * Returns:
     *     Collection<int, array{student: User, class: ClassName, reason: string, tone: string, rate: ?float}>
     */
    public function studentsNeedingAttention(Collection $classes, int $limit = 5): Collection
    {
        $classIds = $classes->where('status', ClassName::STATUS_ACTIVE)->pluck('id')->all();

        if ($classIds === []) {
            return collect();
        }

        $enrollments = Enrollment::query()->active()->whereIn('class_id', $classIds)
            ->with('student:id,name,profile_photo_path')
            ->get(['id', 'class_id', 'student_id']);

        $totals = Attendance::query()
            ->selectRaw('class_id, student_id, count(*) as total, sum(case when status in (?, ?) then 1 else 0 end) as attended', Attendance::ATTENDED_STATUSES)
            ->whereIn('class_id', $classIds)
            ->groupBy('class_id', 'student_id')
            ->get()
            ->keyBy(fn ($row) => $row->class_id.':'.$row->student_id);

        $recent = Attendance::query()
            ->whereIn('class_id', $classIds)
            ->whereRaw('date(attendance_date) >= ?', [self::today()->subDays(42)->toDateString()])
            ->orderByDesc('attendance_date')
            ->get(['class_id', 'student_id', 'status', 'attendance_date'])
            ->groupBy(fn ($row) => $row->class_id.':'.$row->student_id);

        $behind = ClassProgress::query()
            ->selectRaw('class_id, student_id, count(*) as total')
            ->whereIn('class_id', $classIds)
            ->where('status', 'in_progress')
            ->groupBy('class_id', 'student_id')
            ->get()
            ->keyBy(fn ($row) => $row->class_id.':'.$row->student_id);

        $byClass = $classes->keyBy('id');

        return $enrollments
            ->map(function (Enrollment $enrollment) use ($totals, $recent, $behind, $byClass) {
                $key = $enrollment->class_id.':'.$enrollment->student_id;
                $total = (int) ($totals->get($key)->total ?? 0);
                $rate = $total > 0 ? round((int) $totals->get($key)->attended / $total * 100, 1) : null;
                $lastTwo = ($recent->get($key) ?? collect())->take(2);
                $missedTwo = $lastTwo->count() === 2
                    && $lastTwo->every(fn ($row) => ! in_array($row->status, Attendance::ATTENDED_STATUSES, true));
                $unfinished = (int) ($behind->get($key)->total ?? 0);

                [$reason, $tone, $weight] = match (true) {
                    $missedTwo => ['Tidak hadir 2 pertemuan terakhir', 'danger', 3],
                    $rate !== null && $total >= 3 && $rate < 75 => ['Kehadiran '.number_format($rate, 0, ',', '.').'%', 'warning', 2],
                    $unfinished >= 2 => [$unfinished.' materi belum tuntas', 'info', 1],
                    default => [null, null, 0],
                };

                return $reason === null || ! $enrollment->student ? null : [
                    'student' => $enrollment->student,
                    'class' => $byClass->get($enrollment->class_id),
                    'reason' => $reason,
                    'tone' => $tone,
                    'rate' => $rate,
                    'weight' => $weight,
                ];
            })
            ->filter()
            ->sortBy([['weight', 'desc'], ['rate', 'asc']])
            ->take($limit)
            ->values();
    }

    /**
     * Whether the class has a weekly slot on the date's weekday.
     *
     * Args:
     *     class (ClassName): The class.
     *     date (CarbonInterface): The date.
     */
    public function isScheduledOn(ClassName $class, CarbonInterface $date): bool
    {
        return Schedule::query()
            ->where('class_id', $class->id)
            ->where('schedules.is_active', true)
            ->where('day_of_week', $date->dayOfWeekIso)
            ->exists();
    }
}
