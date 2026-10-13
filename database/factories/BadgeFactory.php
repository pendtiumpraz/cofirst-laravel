<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Badge>
 */
class BadgeFactory extends Factory
{
    protected $model = Badge::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucwords($this->faker->unique()->words(2, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'description' => $this->faker->sentence(),
            'icon' => 'star',
            'image_path' => null,
            'category' => $this->faker->randomElement([Badge::CATEGORY_ACADEMIC, Badge::CATEGORY_SOCIAL, Badge::CATEGORY_ATTENDANCE, Badge::CATEGORY_SPECIAL]),
            'level' => $this->faker->randomElement([Badge::LEVEL_BRONZE, Badge::LEVEL_SILVER, Badge::LEVEL_GOLD]),
            'points_required' => 10,
            'criteria' => ['total_points' => 100],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
