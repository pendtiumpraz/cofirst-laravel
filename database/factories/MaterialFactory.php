<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\Syllabus;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition()
    {
        return [
            'syllabus_id' => Syllabus::factory(),
            'title' => $this->faker->sentence,
            'content' => $this->faker->text,
            'description' => $this->faker->paragraph,
            'meeting_start' => $this->faker->numberBetween(1, 10),
            'meeting_end' => $this->faker->numberBetween(1, 12),
            'type' => $this->faker->randomElement(['document', 'video', 'exercise', 'quiz', 'project']),
            'file_path' => null,
            'external_url' => $this->faker->optional()->url,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'order' => $this->faker->numberBetween(1, 5),
        ];
    }
}
