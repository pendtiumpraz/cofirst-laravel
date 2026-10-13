<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPoint>
 */
class UserPointFactory extends Factory
{
    protected $model = UserPoint::class;

    /**
     * Define the model's default state (balance = earned - spent).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $earned = $this->faker->numberBetween(0, 900);
        $spent = $this->faker->numberBetween(0, intdiv($earned, 2));
        $level = 1;

        foreach (UserPoint::LEVEL_POINTS as $candidate => $required) {
            if ($earned >= $required) {
                $level = $candidate;
            }
        }

        return [
            'user_id' => User::factory()->student(),
            'points' => $earned - $spent,
            'total_earned' => $earned,
            'total_spent' => $spent,
            'level' => $level,
            'current_streak' => $this->faker->numberBetween(0, 7),
            'longest_streak' => $this->faker->numberBetween(7, 14),
            'last_activity_date' => now()->subDay()->toDateString(),
        ];
    }
}
