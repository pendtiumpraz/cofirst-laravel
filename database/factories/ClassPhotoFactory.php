<?php

namespace Database\Factories;

use App\Models\ClassName;
use App\Models\ClassPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassPhoto>
 */
class ClassPhotoFactory extends Factory
{
    protected $model = ClassPhoto::class;

    /**
     * Define the model's default state (no file attached).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_id' => ClassName::factory()->active(),
            'title' => ucfirst($this->faker->words(3, true)),
            'description' => $this->faker->sentence(),
            'date_taken' => now()->subWeek()->toDateString(),
            'photo_path' => null,
            'caption' => $this->faker->sentence(),
            'uploaded_by' => User::factory()->teacher(),
        ];
    }
}
