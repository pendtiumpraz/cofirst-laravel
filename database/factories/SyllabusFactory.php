<?php

namespace Database\Factories;

use App\Models\Syllabus;
use App\Models\Curriculum;
use Illuminate\Database\Eloquent\Factories\Factory;

class SyllabusFactory extends Factory
{
    protected $model = Syllabus::class;

    public function definition()
    {
        return [
            'curriculum_id' => Curriculum::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'meeting_number' => $this->faker->numberBetween(1, 12),
            'learning_objectives' => $this->faker->paragraph,
            'activities' => $this->faker->paragraph,
            'duration_minutes' => $this->faker->randomElement([60, 90, 120]),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
