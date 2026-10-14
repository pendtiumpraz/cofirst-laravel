<?php

namespace App\Services\Scheduling;

use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Detects clashes for a proposed weekly slot: the same teacher, the same
 * physical room or the same student at overlapping times on the same day.
 *
 * Only active rows of planned/active classes count (Schedule::forCalendar()).
 * Rows of the very same session (same class, day, times and room) are not
 * clashes — that is how a group class is stored.
 */
final class ScheduleConflictChecker
{
    /** Room values that are not a physical place (never clash). */
    private const VIRTUAL_ROOMS = ['online', 'daring', 'zoom', 'google meet', 'meet', 'teams', 'rumah siswa', 'kunjungan rumah'];

    /**
     * Whether a room value names a physical room that can be double-booked.
     */
    public static function isPhysicalRoom(?string $room): bool
    {
        $value = mb_strtolower(trim((string) $room));
        if ($value === '' || $value === '-') {
            return false;
        }

        foreach (self::VIRTUAL_ROOMS as $virtual) {
            if (str_contains($value, $virtual)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check a proposed slot.
     *
     * Args:
     *     slot (array): class_id, day_of_week (1-7), start_time "H:i", end_time "H:i",
     *         room (?string), teacher_id (?int), enrollment_ids (int[]).
     *     ignoreIds (int[]): Schedule ids being replaced (the slot under edit).
     *
     * Returns:
     *     array<string, string>: field => Indonesian error message (empty when clear).
     */
    public function check(array $slot, array $ignoreIds = []): array
    {
        $start = $this->time($slot['start_time'] ?? null);
        $end = $this->time($slot['end_time'] ?? null);
        if ($start === null || $end === null || $start >= $end) {
            return [];
        }

        $classId = (int) $slot['class_id'];
        $room = isset($slot['room']) ? trim((string) $slot['room']) : null;
        $roomKey = mb_strtolower((string) $room);

        $overlapping = Schedule::query()
            ->forCalendar()
            ->where('schedules.day_of_week', (int) $slot['day_of_week'])
            ->where('schedules.start_time', '<', $end)
            ->where('schedules.end_time', '>', $start)
            ->when($ignoreIds !== [], fn ($q) => $q->whereNotIn('schedules.id', $ignoreIds))
            ->with(['className:id,name', 'teacherAssignment:id,teacher_id', 'teacherAssignment.teacher:id,name', 'enrollment:id,student_id'])
            ->get();

        $isSameSession = fn (Schedule $row) => (int) $row->class_id === $classId
            && $row->getRawOriginal('start_time') === $start
            && $row->getRawOriginal('end_time') === $end
            && mb_strtolower(trim((string) $row->room)) === $roomKey;

        $errors = [];

        // The very same slot already exists for this class (only when creating a new one).
        if ($overlapping->contains($isSameSession)) {
            $errors['start_time'] = 'Kelas ini sudah memiliki jadwal pada hari, jam dan ruang yang sama. Ubah jadwal tersebut atau pilih waktu lain.';
        }

        $others = $overlapping->reject($isSameSession);

        $teacherId = isset($slot['teacher_id']) ? (int) $slot['teacher_id'] : null;
        if ($teacherId) {
            $clash = $others->first(fn (Schedule $row) => (int) $row->teacherAssignment?->teacher_id === $teacherId);
            if ($clash) {
                $errors['teacher_id'] = sprintf(
                    'Bentrok: %s sudah mengajar %s pada %s %s.',
                    $clash->teacherAssignment?->teacher?->name ?? 'guru ini',
                    $clash->className?->name ?? 'kelas lain',
                    Schedule::DAYS[$clash->day_of_week] ?? '',
                    $this->range($clash)
                );
            }
        }

        if (self::isPhysicalRoom($room)) {
            $clash = $others->first(fn (Schedule $row) => mb_strtolower(trim((string) $row->room)) === $roomKey);
            if ($clash) {
                $errors['room'] = sprintf(
                    'Bentrok: %s sudah dipakai %s pada %s %s.',
                    $room,
                    $clash->className?->name ?? 'kelas lain',
                    Schedule::DAYS[$clash->day_of_week] ?? '',
                    $this->range($clash)
                );
            }
        }

        $enrollmentIds = array_map('intval', $slot['enrollment_ids'] ?? []);
        if ($enrollmentIds !== []) {
            $studentIds = Enrollment::whereIn('id', $enrollmentIds)->pluck('student_id')->all();
            $busy = $others->filter(fn (Schedule $row) => in_array((int) $row->enrollment?->student_id, $studentIds, true));
            if ($busy->isNotEmpty()) {
                $names = User::whereIn('id', $busy->pluck('enrollment.student_id')->unique())->limit(3)->pluck('name');
                $first = $busy->first();
                $errors['enrollment_ids'] = sprintf(
                    'Bentrok: %s sudah punya jadwal %s pada %s %s.',
                    $names->implode(', ').($busy->pluck('enrollment.student_id')->unique()->count() > 3 ? ' dan lainnya' : ''),
                    $first->className?->name ?? 'kelas lain',
                    Schedule::DAYS[$first->day_of_week] ?? '',
                    $this->range($first)
                );
            }
        }

        return $errors;
    }

    /**
     * Normalise "H:i" or "H:i:s" to "H:i:s" (null when invalid).
     */
    private function time(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', trim($value), $m)) {
            return null;
        }

        return sprintf('%02d:%02d:%02d', (int) $m[1], (int) $m[2], (int) ($m[3] ?? 0));
    }

    /**
     * "16.00–17.30" for a row.
     */
    private function range(Schedule $row): string
    {
        return str_replace(':', '.', substr((string) $row->getRawOriginal('start_time'), 0, 5)).'–'
            .str_replace(':', '.', substr((string) $row->getRawOriginal('end_time'), 0, 5));
    }

    /**
     * Collection helper for callers that already hold rows.
     *
     * @param  Collection<int, Schedule>  $rows
     * @return array<int, int>
     */
    public static function ids(Collection $rows): array
    {
        return $rows->pluck('id')->map(fn ($id) => (int) $id)->all();
    }
}
