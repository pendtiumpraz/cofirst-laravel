<?php

namespace Database\Factories;

use App\Models\Syllabus;
use App\Models\Curriculum;
use Illuminate\Database\Eloquent\Factories\Factory;

class SyllabusFactory extends Factory
{
    protected $model = Syllabus::class;

    public function definition()
    {
        return [
            'curriculum_id' => Curriculum::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ];
    }
}
