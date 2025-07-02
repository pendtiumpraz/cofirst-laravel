<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\Enrollment;
use App\Models\ClassName;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition()
    {
        $class = ClassName::factory()->create();
        $student = User::factory()->create()->assignRole('student');
        $teacher = User::factory()->create()->assignRole('teacher');

        return [
            'class_id' => $class->id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'recommendations' => $this->faker->sentence,
            'progress_percentage' => $this->faker->numberBetween(0, 100),
            'report_date' => $this->faker->date(),
            'type' => $this->faker->randomElement(['weekly', 'monthly', 'final']),
        ];
    }
}