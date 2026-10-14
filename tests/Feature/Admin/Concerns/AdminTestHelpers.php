<?php

namespace Tests\Feature\Admin\Concerns;

use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\User;
use Database\Seeders\RoleSeeder;

/**
 * Shared fixtures for the admin-core feature tests: seeded roles/permissions
 * (RoleSeeder) and small factories for staff, students and classes.
 */
trait AdminTestHelpers
{
    /**
     * Seed roles and permissions exactly like production.
     */
    protected function seedRoles(): void
    {
        $this->seed(RoleSeeder::class);
    }

    protected function superadmin(array $attributes = []): User
    {
        return User::factory()->superadmin()->create($attributes);
    }

    protected function admin(array $attributes = []): User
    {
        return User::factory()->admin()->create($attributes);
    }

    protected function teacher(array $attributes = []): User
    {
        return User::factory()->teacher()->create($attributes);
    }

    protected function student(array $attributes = []): User
    {
        return User::factory()->student()->create($attributes);
    }

    protected function parentUser(array $attributes = []): User
    {
        return User::factory()->parent()->create($attributes);
    }

    /**
     * An active class with one teacher and the given capacity.
     */
    protected function activeClass(int $capacity = 5, ?User $teacher = null, array $attributes = []): ClassName
    {
        $teacher ??= $this->teacher();

        return ClassName::factory()->active()->withTeacher($teacher)->create(array_merge([
            'max_students' => $capacity,
            'type' => 'group_class_3_5_kids',
        ], $attributes));
    }

    /**
     * Enroll a (new) student into the class.
     */
    protected function enroll(ClassName $class, ?User $student = null, string $status = 'active'): Enrollment
    {
        return Enrollment::factory()->create([
            'class_id' => $class->id,
            'student_id' => ($student ?? $this->student())->id,
            'status' => $status,
        ]);
    }
}
