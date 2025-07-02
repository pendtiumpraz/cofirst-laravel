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
        ];
    }
}
