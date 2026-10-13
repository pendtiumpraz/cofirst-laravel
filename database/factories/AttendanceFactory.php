<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\ClassName;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    /**
     * Define the model's default state (present at a past meeting).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_id' => ClassName::factory()->active(),
            'student_id' => User::factory()->student(),
            'schedule_id' => null,
            'teacher_id' => null,
            'meeting_number' => $this->faker->numberBetween(1, 8),
            'attendance_date' => $this->faker->unique()->dateTimeBetween('-8 weeks', '-1 day')->format('Y-m-d'),
            'status' => Attendance::STATUS_PRESENT,
            'check_in_time' => '09:00:00',
            'minutes_late' => null,
            'notes' => null,
        ];
    }

    /** Absent student. */
    public function absent(): static
    {
        return $this->state(fn () => ['status' => Attendance::STATUS_ABSENT, 'check_in_time' => null]);
    }
}
