<?php

namespace Database\Factories;

use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PointTransaction>
 */
class PointTransactionFactory extends Factory
{
    protected $model = PointTransaction::class;

    /**
     * Define the model's default state (points earned for attendance).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'points' => PointTransaction::POINTS[PointTransaction::REASON_ATTENDANCE],
            'type' => PointTransaction::TYPE_EARNED,
            'reason' => PointTransaction::REASON_ATTENDANCE,
            'description' => 'Hadir di kelas',
            'related_type' => null,
            'related_id' => null,
            'metadata' => null,
        ];
    }
}
