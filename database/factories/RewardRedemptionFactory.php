<?php

namespace Database\Factories;

use App\Models\Reward;
use App\Models\RewardRedemption;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RewardRedemption>
 */
class RewardRedemptionFactory extends Factory
{
    protected $model = RewardRedemption::class;

    /**
     * Define the model's default state (pending).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'reward_id' => Reward::factory(),
            'points_spent' => fn (array $attributes) => Reward::find($attributes['reward_id'])?->points_cost ?? 100,
            'status' => RewardRedemption::STATUS_PENDING,
            'notes' => null,
            'redeemed_at' => now()->subDays(2),
            'processed_at' => null,
            'processed_by' => null,
        ];
    }
}
