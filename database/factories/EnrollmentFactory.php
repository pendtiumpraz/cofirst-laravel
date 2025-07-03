<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\ClassName;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition()
    {
        return [
            'class_id' => ClassName::factory(), // Diperbaiki dari class_name_id
            'student_id' => User::factory()->create()->assignRole('student'),
            'enrollment_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'completed', 'dropped']),
            'is_active' => true,
        ];
    }
}
