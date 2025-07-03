<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\TeacherAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition()
    {
        return [
            'class_id' => ClassName::factory(),
            'day_of_week' => $this->faker->numberBetween(1, 7), // 1=Senin, 7=Minggu
            'start_time' => $this->faker->time('H:i'),
            'end_time' => $this->faker->time('H:i'),
            'room' => $this->faker->optional()->randomElement(['Room A', 'Room B', 'Online', 'Lab 1', 'Lab 2']),
            'enrollment_id' => Enrollment::factory(),
            'teacher_assignment_id' => TeacherAssignment::factory(),
            'is_active' => true,
        ];
    }
}
