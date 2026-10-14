<?php

namespace App\Services\Scheduling;

use App\Models\ClassName;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * One weekly session of a class: all schedule rows of the same class, day,
 * start/end time and room (the schema stores one row per enrolled student).
 */
final class ScheduleSlot
{
    /**
     * Args:
     *     key (string): Stable slot key (class|day|start|end|room).
     *     rows (Collection<int, Schedule>): The schedule rows of this slot.
     */
    public function __construct(
        public readonly string $key,
        public readonly Collection $rows,
    ) {
    }

    /** Representative schedule row (lowest id) used for links. */
    public function schedule(): Schedule
    {
        return $this->rows->sortBy('id')->first();
    }

    /** Id of the representative row. */
    public function id(): int
    {
        return (int) $this->schedule()->id;
    }

    /** The class of the slot. */
    public function class(): ?ClassName
    {
        return $this->schedule()->className;
    }

    /** ISO weekday (1 = Senin). */
    public function day(): int
    {
        return (int) $this->schedule()->day_of_week;
    }

    /** Indonesian day name. */
    public function dayName(): string
    {
        return Schedule::DAYS[$this->day()] ?? '';
    }

    /** Start time "H:i". */
    public function start(): string
    {
        return substr((string) $this->schedule()->getRawOriginal('start_time'), 0, 5);
    }

    /** End time "H:i". */
    public function end(): string
    {
        return substr((string) $this->schedule()->getRawOriginal('end_time'), 0, 5);
    }

    /** Time range in Indonesian style, e.g. "16.00–17.30". */
    public function timeRange(): string
    {
        return str_replace(':', '.', $this->start()).'–'.str_replace(':', '.', $this->end());
    }

    /** Room or location (null when unset). */
    public function room(): ?string
    {
        return $this->schedule()->room;
    }

    /** The teacher of the slot (assignment teacher, else the class's first teacher). */
    public function teacher(): ?User
    {
        foreach ($this->rows as $row) {
            if ($row->relationLoaded('teacherAssignment') && $row->teacherAssignment?->teacher) {
                return $row->teacherAssignment->teacher;
            }
        }

        $class = $this->class();

        return $class && $class->relationLoaded('teachers') ? $class->teachers->first() : null;
    }

    /**
     * Students in the slot (only when enrollment.student is loaded).
     *
     * @return Collection<int, User>
     */
    public function students(): Collection
    {
        return $this->rows
            ->map(fn (Schedule $row) => $row->relationLoaded('enrollment') ? $row->enrollment?->student : null)
            ->filter()
            ->unique('id')
            ->values();
    }

    /** Number of student rows in the slot. */
    public function studentCount(): int
    {
        return $this->rows->whereNotNull('enrollment_id')->count();
    }

    /** Whether at least one row of the slot is active. */
    public function isActive(): bool
    {
        return $this->rows->contains(fn (Schedule $row) => (bool) $row->is_active);
    }

    /** Minutes since midnight of the start (for sorting/overlap). */
    public function startMinutes(): int
    {
        [$h, $m] = array_map('intval', explode(':', $this->start().':0'));

        return $h * 60 + $m;
    }

    /** Minutes since midnight of the end. */
    public function endMinutes(): int
    {
        [$h, $m] = array_map('intval', explode(':', $this->end().':0'));

        return $h * 60 + $m;
    }
}
