<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Profile::class;

    /**
     * Define the model's default state (an adult by default).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fn (array $attributes) => User::find($attributes['user_id'])?->name ?? $this->faker->name(),
            'address' => $this->faker->address(),
            'phone_number' => $this->faker->numerify('08##########'),
            'birth_date' => $this->faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'bio' => $this->faker->sentence(),
            'avatar' => null,
        ];
    }

    /**
     * A child profile aged between 8 and 17.
     */
    public function child(): static
    {
        return $this->state(fn () => [
            'birth_date' => $this->faker->dateTimeBetween('-17 years', '-8 years')->format('Y-m-d'),
        ]);
    }
}
