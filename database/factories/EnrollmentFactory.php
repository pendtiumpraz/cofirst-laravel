<?php

namespace Database\Factories;

use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    /**
     * Define the model's default state (an active, paid enrollment).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_id' => ClassName::factory(),
            'course_id' => fn (array $attributes) => ClassName::find($attributes['class_id'])?->course_id,
            'student_id' => User::factory()->student(),
            'enrollment_date' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'status' => Enrollment::STATUS_ACTIVE,
            'payment_status' => 'paid',
            'is_active' => true,
        ];
    }

    /** A registration awaiting payment/approval. */
    public function pending(): static
    {
        return $this->state(fn () => ['status' => Enrollment::STATUS_PENDING, 'payment_status' => 'unpaid']);
    }

    /** A finished enrollment. */
    public function completed(): static
    {
        return $this->state(fn () => ['status' => Enrollment::STATUS_COMPLETED]);
    }
}
