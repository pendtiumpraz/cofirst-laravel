<?php

namespace Database\Factories;

use App\Models\ClassName;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherAssignment>
 *
 * Note: this only writes the history row. To assign a teacher properly use
 * ClassName::syncTeachers(), which also updates the class_teacher pivot.
 */
class TeacherAssignmentFactory extends Factory
{
    protected $model = TeacherAssignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_id' => ClassName::factory(),
            'teacher_id' => User::factory()->teacher(),
            'assigned_at' => now()->subMonth(),
            'unassigned_at' => null,
        ];
    }
}
