<?php

namespace App\Services\Academic;

use App\Models\Material;
use App\Models\MaterialAccess;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Records when students open learning materials, with data minimisation.
 *
 * UU PDP: students are mostly children, so the log is throttled (one row per student
 * and material per 30 minutes), can be reduced to "opened / last opened"
 * (academic.material_tracking_mode = minimal) and is purged after
 * academic.retention_material_access_days. Durations are not measured, so no
 * "time spent" figure is shown anywhere.
 */
class MaterialAccessService
{
    /** Minutes during which repeated opens of the same material count once. */
    public const THROTTLE_MINUTES = 30;

    /** Record that a student opened (or downloaded) a material. */
    public function record(User $student, Material $material): void
    {
        $latest = MaterialAccess::query()
            ->where('student_id', $student->id)
            ->where('material_id', $material->id)
            ->latest('accessed_at')
            ->first();

        if (AcademicSettings::trackingMode() === AcademicSettings::TRACKING_MINIMAL) {
            $latest
                ? $latest->update(['accessed_at' => now()])
                : MaterialAccess::create(['student_id' => $student->id, 'material_id' => $material->id, 'accessed_at' => now(), 'access_duration_seconds' => 0]);

            return;
        }

        if ($latest && $latest->accessed_at && $latest->accessed_at->gt(now()->subMinutes(self::THROTTLE_MINUTES))) {
            return;
        }

        MaterialAccess::create([
            'student_id' => $student->id,
            'material_id' => $material->id,
            'accessed_at' => now(),
            'access_duration_seconds' => 0,
        ]);
    }

    /**
     * Access statistics of one student for several materials (one query).
     *
     * Returns:
     *     Collection<int, array{count: int, first: ?string, last: ?string}>: keyed by material id.
     */
    public function statsFor(int $studentId, Collection|array $materialIds): Collection
    {
        $ids = collect($materialIds)->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return MaterialAccess::query()
            ->where('student_id', $studentId)
            ->whereIn('material_id', $ids)
            ->selectRaw('material_id, count(*) as total, min(accessed_at) as first_at, max(accessed_at) as last_at')
            ->groupBy('material_id')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->material_id => [
                'count' => (int) $row->total,
                'first' => $row->first_at,
                'last' => $row->last_at,
            ]]);
    }

    /**
     * Delete access rows older than the retention period.
     *
     * Returns:
     *     int: Rows deleted.
     */
    public function purgeExpired(): int
    {
        $days = max(30, (int) AcademicSettings::get('academic.retention_material_access_days'));

        return MaterialAccess::query()->where('accessed_at', '<', now()->subDays($days))->delete();
    }
}
