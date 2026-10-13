<?php

namespace Database\Factories;

use App\Models\ClassName;
use App\Models\ProjectGallery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectGallery>
 */
class ProjectGalleryFactory extends Factory
{
    protected $model = ProjectGallery::class;

    /**
     * Define the model's default state (no image file).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'class_id' => ClassName::factory()->active(),
            'title' => ucfirst($this->faker->words(3, true)),
            'description' => $this->faker->paragraph(),
            'photo_path' => null,
            'thumbnail_path' => null,
            'is_featured' => false,
            'views' => $this->faker->numberBetween(0, 200),
        ];
    }
}
