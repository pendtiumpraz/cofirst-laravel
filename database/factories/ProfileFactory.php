<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // Mengambil user_id dari konteks saat factory dipanggil di seeder
            'user_id' => User::factory(), 
            
            // Mengisi full_name dari nama user terkait
            'full_name' => function (array $attributes) {
                return User::find($attributes['user_id'])->name;
            },
            
            'address' => $this->faker->address,
            'phone_number' => $this->faker->phoneNumber,
            'birth_date' => $this->faker->date(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'bio' => $this->faker->sentence,
            'avatar' => null, // atau $this->faker->imageUrl()
        ];
    }
}