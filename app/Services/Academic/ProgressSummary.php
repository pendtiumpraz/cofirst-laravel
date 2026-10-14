<?php

namespace App\Services\Academic;

use App\Models\ClassName;
use App\Models\Syllabus;
use Illuminate\Support\Collection;

/**
 * Learning progress of one student in one class (read model for the views).
 *
 * `meetings` holds one entry per active syllabus of the class curriculum:
 *   ['syllabus' => Syllabus, 'number' => int, 'status' => completed|in_progress|not_started,
 *    'completed_at' => ?Carbon, 'notes' => ?string, 'unlocked' => bool]
 */
class ProgressSummary
{
    /**
     * Args:
     *     class (ClassName): The class.
     *     meetings (Collection): One entry per meeting (see class docblock).
     *     unlockedMeetings (array<int, int>): Meeting numbers whose materials are open.
     */
    public function __construct(
        public readonly ClassName $class,
        public readonly Collection $meetings,
        public readonly array $unlockedMeetings,
    ) {
    }

    /** Whether the class has a curriculum with at least one active meeting. */
    public function hasCurriculum(): bool
    {
        return $this->class->curriculum_id !== null && $this->meetings->isNotEmpty();
    }

    /** Number of meetings in the curriculum. */
    public function total(): int
    {
        return $this->meetings->count();
    }

    /** Number of completed meetings. */
    public function completed(): int
    {
        return $this->meetings->where('status', 'completed')->count();
    }

    /** Number of meetings in progress. */
    public function inProgress(): int
    {
        return $this->meetings->where('status', 'in_progress')->count();
    }

    /** Completion percentage (0-100, one decimal). */
    public function percent(): float
    {
        return $this->total() > 0 ? round($this->completed() / $this->total() * 100, 1) : 0.0;
    }

    /** The first meeting that is not completed yet. */
    public function nextMeeting(): ?Syllabus
    {
        return $this->meetings->firstWhere(fn ($m) => $m['status'] !== 'completed')['syllabus'] ?? null;
    }

    /** Whether a meeting number is unlocked. */
    public function isMeetingUnlocked(int $number): bool
    {
        return in_array($number, $this->unlockedMeetings, true);
    }

    /** Short Indonesian label of the overall state. */
    public function label(): string
    {
        return match (true) {
            ! $this->hasCurriculum() => 'Kurikulum belum ditetapkan',
            $this->completed() === 0 => 'Belum dimulai',
            $this->completed() >= $this->total() => 'Selesai',
            default => 'Sedang berjalan',
        };
    }
}
