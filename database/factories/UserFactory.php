<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 *
 * Role states (admin(), teacher(), student(), parent(), finance(), superadmin())
 * assign the role after creating and create the role when it does not exist yet,
 * so they also work in tests that did not run the RoleSeeder.
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the account is deactivated.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    /**
     * Assign a role after creating (the role is created when missing).
     *
     * Args:
     *     role (string): Role name.
     */
    public function withRole(string $role): static
    {
        return $this->afterCreating(function (User $user) use ($role) {
            $user->assignRole(Role::findOrCreate($role, 'web'));
        });
    }

    /** User with the superadmin role. */
    public function superadmin(): static
    {
        return $this->withRole('superadmin');
    }

    /** User with the admin role. */
    public function admin(): static
    {
        return $this->withRole('admin');
    }

    /** User with the teacher role. */
    public function teacher(): static
    {
        return $this->withRole('teacher');
    }

    /** User with the student role. */
    public function student(): static
    {
        return $this->withRole('student');
    }

    /** User with the parent role. */
    public function parent(): static
    {
        return $this->withRole('parent');
    }

    /** User with the finance role. */
    public function finance(): static
    {
        return $this->withRole('finance');
    }

    /**
     * Also create a profile (full_name = user name).
     *
     * Args:
     *     attributes (array): Profile attribute overrides (e.g. birth_date).
     */
    public function withProfile(array $attributes = []): static
    {
        return $this->afterCreating(function (User $user) use ($attributes) {
            Profile::factory()->create(array_merge(['user_id' => $user->id, 'full_name' => $user->name], $attributes));
        });
    }
}
