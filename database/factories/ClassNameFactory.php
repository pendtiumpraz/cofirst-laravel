<?php

namespace Database\Factories;

use App\Models\ClassName;
use App\Models\Course;
use App\Models\User;
use App\Models\Curriculum;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassNameFactory extends Factory
{
    protected $model = ClassName::class;

    public function definition()
    {
        $teachers = User::role('teacher')->get();
        $curriculums = Curriculum::all();

        return [
            'course_id' => Course::factory(),
            'teacher_id' => $teachers->isNotEmpty() ? $teachers->random()->id : User::factory()->create()->assignRole('teacher'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'start_date' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+3 months')->format('Y-m-d'),
            'max_students' => 20,
            'status' => 'planned',
            'delivery_method' => $this->faker->randomElement(['online', 'offline']), // Kolom baru
            'type' => $this->faker->randomElement([
                'private_home_call',
                'private_office_1on1',
                'private_online_1on1',
                'public_school_extracurricular',
                'offline_seminar',
                'online_webinar',
                'group_class_3_5_kids',
            ]), // Nilai enum yang diperluas
            'curriculum_id' => $curriculums->isNotEmpty() ? $curriculums->random()->id : null, // Kolom baru
        ];
    }
}