<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Course::factory()->create([
            'name' => 'Pemrograman Web dengan Laravel',
            'description' => 'Kursus komprehensif untuk belajar framework Laravel dari dasar hingga mahir.',
            'price' => 3500000,
        ]);

        Course::factory()->create([
            'name' => 'Dasar-Dasar JavaScript Modern',
            'description' => 'Pelajari fundamental JavaScript ES6+, DOM Manipulation, dan konsep asynchronous.',
            'price' => 2500000,
        ]);

        Course::factory()->create([
            'name' => 'UI/UX Design untuk Pemula',
            'description' => 'Belajar prinsip-prinsip desain antarmuka dan pengalaman pengguna dengan Figma.',
            'price' => 2000000,
        ]);
    }
}