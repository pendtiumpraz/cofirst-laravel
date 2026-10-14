<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Schedule;
use Database\Seeders\Data\DemoBlueprint;
use Database\Seeders\Data\DemoContext;
use Illuminate\Database\Seeder;

/**
 * Weekly schedules for the demo classes.
 *
 * The current schema stores one schedule row per enrollment (enrollment_id) with
 * the teacher assignment of a teacher of the same class. Every pending, active or
 * completed enrollment gets the class's slot; slots of completed classes are
 * inactive (kept as history for meeting reports). Keyed by (class_id,
 * enrollment_id), so re-running is idempotent.
 */
class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $count = 0;

        foreach (DemoBlueprint::classes() as $code => $plan) {
            $class = DemoContext::classByCode($code);
            $assignmentId = $class->activeTeacherAssignments()->orderBy('id')->value('id');

            $enrollments = $class->enrollments()
                ->whereIn('status', [Enrollment::STATUS_PENDING, Enrollment::STATUS_ACTIVE, Enrollment::STATUS_COMPLETED])
                ->orderBy('id')
                ->get();

            foreach ($enrollments as $enrollment) {
                Schedule::updateOrCreate(
                    ['class_id' => $class->id, 'enrollment_id' => $enrollment->id],
                    [
                        'teacher_assignment_id' => $assignmentId,
                        'day_of_week' => $plan['day'],
                        'start_time' => $plan['start'],
                        'end_time' => $plan['end'],
                        'room' => $plan['room'],
                        'is_active' => $plan['status'] !== 'completed',
                    ]
                );
                $count++;
            }
        }

        $this->command?->info("Demo schedules: {$count} weekly slots.");
    }
}
