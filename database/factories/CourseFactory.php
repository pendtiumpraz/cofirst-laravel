<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucwords($this->faker->unique()->words(3, true));

        return [
            'name' => $name,
            // Reason: courses.slug is unique; a random suffix avoids collisions.
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(10, 50) * 100000,
            'level' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'duration_weeks' => $this->faker->randomElement([4, 8, 12]),
            'is_active' => true,
        ];
    }
}
