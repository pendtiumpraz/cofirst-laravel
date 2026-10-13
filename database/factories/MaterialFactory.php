<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\Syllabus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    /**
     * Define the model's default state. meeting_start/end follow the syllabus.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'syllabus_id' => Syllabus::factory(),
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->text(),
            'description' => $this->faker->paragraph(),
            'meeting_start' => fn (array $attributes) => Syllabus::find($attributes['syllabus_id'])?->meeting_number ?? 1,
            'meeting_end' => fn (array $attributes) => $attributes['meeting_start'],
            'type' => $this->faker->randomElement(['document', 'video', 'exercise', 'quiz', 'project']),
            'file_path' => null,
            'external_url' => null,
            'status' => 'active',
            'order' => $this->faker->numberBetween(1, 5),
        ];
    }
}
