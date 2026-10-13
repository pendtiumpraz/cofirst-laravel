<?php

namespace Database\Factories;

use App\Models\ClassName;
use App\Models\ClassReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassReport>
 *
 * Curriculum/syllabus/material/schedule references are optional (nullable).
 */
class ClassReportFactory extends Factory
{
    protected $model = ClassReport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => User::factory()->teacher(),
            'class_id' => ClassName::factory()->active(),
            'student_id' => User::factory()->student(),
            'schedule_id' => null,
            'report_date' => now()->subWeek()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:30',
            'meeting_number' => 1,
            'curriculum_id' => null,
            'syllabus_id' => null,
            'material_id' => null,
            'learning_concept' => $this->faker->sentence(),
            'remember_understanding' => $this->faker->sentence(),
            'understand_comprehension' => $this->faker->sentence(),
            'apply_application' => $this->faker->sentence(),
            'analyze_analysis' => null,
            'evaluate_evaluation' => null,
            'create_creation' => null,
            'notes_recommendations' => $this->faker->sentence(),
            'follow_up_notes' => null,
            'learning_media_link' => null,
        ];
    }
}
