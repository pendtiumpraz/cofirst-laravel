<?php

namespace Database\Factories;

use App\Models\TeacherAssignment;
use App\Models\ClassName;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherAssignmentFactory extends Factory
{
    protected $model = TeacherAssignment::class;

    public function definition()
    {
        return [
            'class_id' => ClassName::factory(), // Diperbaiki dari class_name_id
            'teacher_id' => User::factory()->create()->assignRole('teacher'),
            'assigned_at' => $this->faker->dateTimeThisYear(),
            'unassigned_at' => null,
        ];
    }
}