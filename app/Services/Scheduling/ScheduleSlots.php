<?php

namespace App\Services\Scheduling;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Groups per-student schedule rows into weekly slots and builds agendas.
 *
 * Reason: the schedules table holds one row per enrollment, so a group class of
 * five children is five rows. Calendars, lists and dashboards must show it once.
 */
final class ScheduleSlots
{
    /**
     * Stable key of the slot a row belongs to.
     */
    public static function key(Schedule $row): string
    {
        return implode('|', [
            $row->class_id,
            $row->day_of_week,
            substr((string) $row->getRawOriginal('start_time'), 0, 5),
            substr((string) $row->getRawOriginal('end_time'), 0, 5),
            mb_strtolower(trim((string) $row->room)),
        ]);
    }

    /**
     * Rows of the same slot as the given row (including itself).
     */
    public static function siblingsQuery(Schedule $row): Builder
    {
        return Schedule::query()
            ->where('class_id', $row->class_id)
            ->where('day_of_week', $row->day_of_week)
            ->where('start_time', $row->getRawOriginal('start_time'))
            ->where('end_time', $row->getRawOriginal('end_time'))
            ->where(fn (Builder $q) => $row->room === null || $row->room === ''
                ? $q->whereNull('room')->orWhere('room', '')
                : $q->where('room', $row->room));
    }

    /**
     * Group rows into slots, ordered by day then start time then class name.
     *
     * @param  Collection<int, Schedule>  $rows
     * @return Collection<int, ScheduleSlot>
     */
    public static function group(Collection $rows): Collection
    {
        return $rows
            ->groupBy(fn (Schedule $row) => self::key($row))
            ->map(fn (Collection $group, string $key) => new ScheduleSlot($key, $group->values()))
            ->sortBy(fn (ScheduleSlot $slot) => sprintf('%d-%04d-%s', $slot->day(), $slot->startMinutes(), mb_strtolower((string) $slot->class()?->name)))
            ->values();
    }

    /**
     * Weekly template: slots per ISO weekday (1 = Senin … 7 = Minggu).
     *
     * @param  Collection<int, Schedule>  $rows
     * @return array<int, Collection<int, ScheduleSlot>>
     */
    public static function byWeekday(Collection $rows): array
    {
        $slots = self::group($rows);
        $week = [];

        foreach (array_keys(Schedule::DAYS) as $day) {
            $week[$day] = $slots->filter(fn (ScheduleSlot $slot) => $slot->day() === $day)->values();
        }

        return $week;
    }

    /**
     * Dated agenda for the next N days, honouring each class's start/end date
     * and skipping inactive rows or classes that are completed/cancelled.
     *
     * @param  Collection<int, Schedule>  $rows
     * @return array<int, array{date: Carbon, is_today: bool, slots: Collection<int, ScheduleSlot>}>
     */
    public static function agenda(Collection $rows, Carbon $from, int $days = 7): array
    {
        $visible = $rows->filter(fn (Schedule $row) => $row->is_active
            && (! $row->relationLoaded('className') || $row->className === null || $row->className->canShowInCalendar()));
        $week = self::byWeekday($visible);
        $agenda = [];
        $today = $from->copy()->startOfDay();

        for ($i = 0; $i < $days; $i++) {
            $date = $today->copy()->addDays($i);
            $day = $date->toDateString();
            $slots = $week[$date->dayOfWeekIso]->filter(function (ScheduleSlot $slot) use ($day) {
                $class = $slot->class();
                if (! $class) {
                    return true;
                }

                // Reason: compare calendar dates as strings; the date casts are UTC
                // midnights while $date is in the business timezone.
                return ($class->start_date === null || $class->start_date->toDateString() <= $day)
                    && ($class->end_date === null || $class->end_date->toDateString() >= $day);
            })->values();

            $agenda[] = ['date' => $date, 'is_today' => $i === 0, 'slots' => $slots];
        }

        return $agenda;
    }

    /**
     * Keys of slots that clash with another slot (same teacher or same physical
     * room at overlapping times on the same day).
     *
     * @param  Collection<int, ScheduleSlot>  $slots
     * @return array<string, array<int, string>> slot key => reasons
     */
    public static function clashes(Collection $slots): array
    {
        $clashes = [];
        $active = $slots->filter(fn (ScheduleSlot $slot) => $slot->isActive())->values();

        foreach ($active->groupBy(fn (ScheduleSlot $slot) => $slot->day()) as $daySlots) {
            $list = $daySlots->values();
            for ($i = 0; $i < $list->count(); $i++) {
                for ($j = $i + 1; $j < $list->count(); $j++) {
                    $a = $list[$i];
                    $b = $list[$j];
                    if ($a->startMinutes() >= $b->endMinutes() || $b->startMinutes() >= $a->endMinutes()) {
                        continue;
                    }
                    $teacherA = $a->teacher()?->id;
                    if ($teacherA && $teacherA === $b->teacher()?->id) {
                        $clashes[$a->key][] = 'guru';
                        $clashes[$b->key][] = 'guru';
                    }
                    if (ScheduleConflictChecker::isPhysicalRoom($a->room())
                        && mb_strtolower(trim((string) $a->room())) === mb_strtolower(trim((string) $b->room()))) {
                        $clashes[$a->key][] = 'ruang';
                        $clashes[$b->key][] = 'ruang';
                    }
                }
            }
        }

        return array_map(fn ($reasons) => array_values(array_unique($reasons)), $clashes);
    }
}
