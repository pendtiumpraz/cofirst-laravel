<?php

namespace Database\Factories;

use App\Models\Curriculum;
use App\Models\Syllabus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Syllabus>
 */
class SyllabusFactory extends Factory
{
    protected $model = Syllabus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'curriculum_id' => Curriculum::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'meeting_number' => $this->faker->numberBetween(1, 12),
            // Newline-separated lists (see App\Models\Casts\MultilineText).
            'learning_objectives' => implode("\n", $this->faker->sentences(3)),
            'activities' => implode("\n", $this->faker->sentences(3)),
            'duration_minutes' => $this->faker->randomElement([60, 90, 120]),
            'status' => 'active',
        ];
    }
}
