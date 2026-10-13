<?php

namespace Database\Factories;

use App\Models\ClassName;
use App\Models\TeacherHandover;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherHandover>
 */
class TeacherHandoverFactory extends Factory
{
    protected $model = TeacherHandover::class;

    /**
     * Define the model's default state (a pending whole-class handover).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_id' => ClassName::factory()->active(),
            'from_teacher_id' => User::factory()->teacher(),
            'to_teacher_id' => User::factory()->teacher(),
            'student_id' => null,
            'type' => TeacherHandover::TYPE_TEMPORARY,
            'effective_from' => now()->addWeek()->toDateString(),
            'effective_until' => now()->addWeeks(3)->toDateString(),
            'reason' => $this->faker->sentence(),
            'handover_notes' => $this->faker->paragraph(),
            'last_meeting_number' => $this->faker->numberBetween(1, 8),
            'status' => TeacherHandover::STATUS_PENDING,
            'requested_by' => fn (array $attributes) => $attributes['from_teacher_id'],
        ];
    }
}
