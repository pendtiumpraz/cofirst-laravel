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
            'schedule_date' => $this->faker->date(),
            'schedule_time' => $this->faker->time(),
            'enrollment_id' => Enrollment::factory(),
            'teacher_assignment_id' => TeacherAssignment::factory(),
            'is_active' => $this->faker->boolean,
        ];
    }
}
