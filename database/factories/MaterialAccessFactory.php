<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\MaterialAccess;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaterialAccess>
 */
class MaterialAccessFactory extends Factory
{
    protected $model = MaterialAccess::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'material_id' => Material::factory(),
            'accessed_at' => now()->subDays($this->faker->numberBetween(1, 30)),
            'access_duration_seconds' => $this->faker->numberBetween(60, 1800),
        ];
    }
}
