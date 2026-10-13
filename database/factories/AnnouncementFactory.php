<?php

namespace Database\Factories;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    /**
     * Define the model's default state (visible from today for two weeks).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->admin(),
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
        ];
    }
}
