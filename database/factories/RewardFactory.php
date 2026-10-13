<?php

namespace Database\Factories;

use App\Models\Reward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reward>
 */
class RewardFactory extends Factory
{
    protected $model = Reward::class;

    /**
     * Define the model's default state (an unlimited digital reward).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucwords($this->faker->unique()->words(3, true)),
            'description' => $this->faker->sentence(),
            'type' => Reward::TYPE_DIGITAL,
            'points_cost' => 100,
            'quantity_available' => null,
            'quantity_redeemed' => 0,
            'image' => null,
            'metadata' => [],
            'is_active' => true,
            'available_from' => null,
            'available_until' => null,
        ];
    }

    /** A reward with limited stock. */
    public function limited(int $quantity): static
    {
        return $this->state(fn () => ['quantity_available' => $quantity, 'type' => Reward::TYPE_PHYSICAL]);
    }
}
