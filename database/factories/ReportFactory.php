<?php

namespace Database\Factories;

use App\Models\ClassName;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    /**
     * Define the model's default state. Related models are created lazily, only
     * when the caller does not pass their ids.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_id' => ClassName::factory(),
            'student_id' => User::factory()->student(),
            'teacher_id' => User::factory()->teacher(),
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(),
            'recommendations' => $this->faker->sentence(),
            'progress_percentage' => $this->faker->numberBetween(0, 100),
            'score' => null,
            'report_date' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'type' => $this->faker->randomElement(['weekly', 'monthly', 'final']),
        ];
    }
}
