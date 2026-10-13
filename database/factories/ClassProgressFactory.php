<?php

namespace Database\Factories;

use App\Models\ClassName;
use App\Models\ClassProgress;
use App\Models\Syllabus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassProgress>
 */
class ClassProgressFactory extends Factory
{
    protected $model = ClassProgress::class;

    /**
     * Define the model's default state (a completed meeting).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'class_id' => ClassName::factory()->active(),
            'syllabus_id' => Syllabus::factory(),
            'material_id' => null,
            'meeting_number' => fn (array $attributes) => Syllabus::find($attributes['syllabus_id'])?->meeting_number ?? 1,
            'status' => 'completed',
            'completed_at' => now()->subWeek(),
            'notes' => null,
        ];
    }
}
