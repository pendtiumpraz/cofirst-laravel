<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Curriculum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Curriculum>
 */
class CurriculumFactory extends Factory
{
    protected $model = Curriculum::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['fast-track', 'regular', 'expert', 'beginner']),
            'status' => 'active',
            'duration_weeks' => 8,
            'objectives' => implode("\n", $this->faker->sentences(3)),
        ];
    }
}
