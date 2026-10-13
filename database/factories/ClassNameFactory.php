<?php

namespace Database\Factories;

use App\Models\ClassName;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<ClassName>
 *
 * States: planned() (default), active(), completed(), withTeacher($user).
 * The curriculum is taken from the class's own course (never another course).
 */
class ClassNameFactory extends Factory
{
    protected $model = ClassName::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'name' => ucwords($this->faker->words(3, true)),
            'description' => $this->faker->paragraph(),
            'start_date' => $this->faker->dateTimeBetween('+1 week', '+1 month')->format('Y-m-d'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+3 months')->format('Y-m-d'),
            'max_students' => 20,
            'status' => ClassName::STATUS_PLANNED,
            'delivery_method' => $this->faker->randomElement(['online', 'offline']),
            'type' => $this->faker->randomElement([
                'private_home_call',
                'private_office_1on1',
                'private_online_1on1',
                'public_school_extracurricular',
                'offline_seminar',
                'online_webinar',
                'group_class_3_5_kids',
            ]),
            'curriculum_id' => fn (array $attributes) => Curriculum::where('course_id', $attributes['course_id'])->value('id'),
            'is_active' => true,
        ];
    }

    /**
     * A running class: started in the past, ends in the future.
     */
    public function active(): static
    {
        return $this->state(fn () => [
            'status' => ClassName::STATUS_ACTIVE,
            'start_date' => now()->subWeeks(4)->toDateString(),
            'end_date' => now()->addWeeks(4)->toDateString(),
        ]);
    }

    /**
     * A finished class.
     */
    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => ClassName::STATUS_COMPLETED,
            'start_date' => now()->subWeeks(12)->toDateString(),
            'end_date' => now()->subWeeks(4)->toDateString(),
        ]);
    }

    /**
     * Assign the given teacher (pivot + teacher_assignments) after creating.
     */
    public function withTeacher(User $teacher): static
    {
        return $this->afterCreating(fn (ClassName $class) => $class->syncTeachers([$teacher->id]));
    }

    /**
     * Attach an existing teacher (when the teacher role and a teacher exist).
     *
     * Reason: class_names.teacher_id was dropped in favour of the class_teacher
     * pivot; syncTeachers() also writes the teacher_assignments history row.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (ClassName $class) {
            if ($class->teachers()->exists() || ! Role::where('name', 'teacher')->exists()) {
                return;
            }

            $teacher = User::role('teacher')->inRandomOrder()->first();

            if ($teacher) {
                $class->syncTeachers([$teacher->id]);
            }
        });
    }
}
