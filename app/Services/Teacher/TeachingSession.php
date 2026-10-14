<?php

namespace App\Services\Teacher;

use App\Models\ClassName;
use Carbon\CarbonImmutable;

/**
 * One dated meeting of a class, expanded from its weekly schedule slot.
 *
 * Schedules store one row per enrollment with the same day/time, so a session
 * groups those rows: one class, one date, one time range. Record counters
 * (attendance, reports) are filled by TeachingSchedule::withRecordStatus().
 */
class TeachingSession
{
    /** Number of attendance rows recorded for this class and date. */
    public int $attendanceCount = 0;

    /** Number of rows with status present or late. */
    public int $attendedCount = 0;

    /** Number of berita acara (class_reports) written for this class and date. */
    public int $reportCount = 0;

    /**
     * Args:
     *     class (ClassName): The class, with course and active_students_count loaded.
     *     date (CarbonImmutable): Meeting date (school timezone, start of day).
     *     start (string): Start time "H:i".
     *     end (string): End time "H:i".
     *     room (string|null): Room or online venue.
     *     scheduleId (int): A representative schedule row (for berita acara links).
     *     studentName (string|null): The student's name for private (single-student) slots.
     */
    public function __construct(
        public readonly ClassName $class,
        public readonly CarbonImmutable $date,
        public readonly string $start,
        public readonly string $end,
        public readonly ?string $room,
        public readonly int $scheduleId,
        public readonly ?string $studentName = null,
    ) {}

    /** Stable key "classId:Y-m-d" used to match aggregated record counts. */
    public function key(): string
    {
        return $this->class->id.':'.$this->date->toDateString();
    }

    /** Number of active students in the class. */
    public function rosterCount(): int
    {
        return (int) ($this->class->active_students_count ?? 0);
    }

    /** Duration in minutes (0 when the times are not a valid range). */
    public function minutes(): int
    {
        [$sh, $sm] = array_map('intval', explode(':', $this->start) + [0, 0]);
        [$eh, $em] = array_map('intval', explode(':', $this->end) + [0, 0]);

        return max(0, ($eh * 60 + $em) - ($sh * 60 + $sm));
    }

    /** Whether the session takes place on the given local date. */
    public function isOn(CarbonImmutable $day): bool
    {
        return $this->date->isSameDay($day);
    }

    /** Whether the session has already ended at the given local time. */
    public function hasEnded(CarbonImmutable $now): bool
    {
        if ($this->date->toDateString() !== $now->toDateString()) {
            return $this->date->lt($now->startOfDay());
        }

        return $this->end <= $now->format('H:i');
    }

    /** Whether the session is running at the given local time. */
    public function isOngoing(CarbonImmutable $now): bool
    {
        return $this->date->toDateString() === $now->toDateString()
            && $this->start <= $now->format('H:i')
            && $this->end > $now->format('H:i');
    }

    /** Whether attendance was recorded for at least one student. */
    public function hasAttendance(): bool
    {
        return $this->attendanceCount > 0;
    }

    /**
     * Number of attending students still without a berita acara.
     *
     * Reason: berita acara are written per attending student, so the expected
     * number is the attended count once attendance exists, else the roster.
     */
    public function missingReports(): int
    {
        $expected = $this->hasAttendance() ? $this->attendedCount : $this->rosterCount();

        return max(0, $expected - $this->reportCount);
    }

    /** Whether the class is held online (delivery method or room name). */
    public function isOnline(): bool
    {
        return $this->class->delivery_method === 'online'
            || ($this->room !== null && str_contains(mb_strtolower($this->room), 'online'));
    }
}
