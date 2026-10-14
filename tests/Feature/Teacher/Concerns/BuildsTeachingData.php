<?php

namespace Tests\Feature\Teacher\Concerns;

use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Builders for teacher-portal tests: a teacher, a running class on a given
 * weekday with N active students (one schedule row per enrollment, as in the
 * real schema).
 */
trait BuildsTeachingData
{
    /** Freeze "now" to Wednesday 14 Oct 2026, 10:00 WIB (03:00 UTC). */
    protected function freezeWednesdayMorning(): void
    {
        $this->travelTo(Carbon::parse('2026-10-14 03:00:00', 'UTC'));
    }

    protected function makeTeacher(array $attributes = []): User
    {
        return User::factory()->teacher()->create($attributes + ['is_active' => true]);
    }

    /**
     * A running class taught by `$teacher` with a weekly slot and active students.
     *
     * Args:
     *     teacher (User): Teacher assigned through the class_teacher pivot.
     *     students (int): Number of active students (each gets a schedule row).
     *     day (int): ISO weekday of the slot (3 = Rabu).
     *     overrides (array): ClassName attribute overrides.
     */
    protected function makeClass(User $teacher, int $students = 2, int $day = 3, array $overrides = []): ClassName
    {
        $class = ClassName::factory()->active()->withTeacher($teacher)->create($overrides + ['max_students' => 10]);

        for ($i = 0; $i < $students; $i++) {
            $student = User::factory()->student()->create();
            $this->enroll($class, $student, $day);
        }

        return $class->refresh();
    }

    /** Enroll a student (active) and give them the class's weekly slot. */
    protected function enroll(ClassName $class, User $student, int $day = 3, string $status = Enrollment::STATUS_ACTIVE): Enrollment
    {
        $enrollment = Enrollment::factory()->create([
            'class_id' => $class->id,
            'course_id' => $class->course_id,
            'student_id' => $student->id,
            'status' => $status,
        ]);

        Schedule::factory()->create([
            'class_id' => $class->id,
            'enrollment_id' => $enrollment->id,
            'day_of_week' => $day,
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'room' => 'Ruang A',
        ]);

        return $enrollment;
    }

    /** Active student ids of a class. */
    protected function studentIds(ClassName $class): array
    {
        return $class->enrollments()->where('status', Enrollment::STATUS_ACTIVE)->pluck('student_id')->map(fn ($id) => (int) $id)->all();
    }
}
