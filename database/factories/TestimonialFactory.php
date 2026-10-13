<?php

namespace Database\Factories;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->parent(),
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(),
            'rating' => $this->faker->numberBetween(4, 5),
            'child_name' => null,
            'child_class' => null,
            'is_active' => true,
            'is_featured' => false,
        ];
    }
}
