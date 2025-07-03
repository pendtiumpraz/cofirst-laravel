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
        $courses = [
            [
                'name' => 'Scratch',
                'description' => 'Pemrograman visual untuk anak-anak menggunakan Scratch. Belajar konsep dasar programming dengan cara yang menyenangkan.',
                'price' => 1500000,
            ],
            [
                'name' => 'Lego Spike (Robotik)',
                'description' => 'Belajar robotika menggunakan Lego Spike Prime. Kombinasi programming dan engineering untuk anak-anak.',
                'price' => 2500000,
            ],
            [
                'name' => 'Microbit',
                'description' => 'Pemrograman mikrokontroler BBC micro:bit. Belajar IoT dan embedded programming untuk pemula.',
                'price' => 1800000,
            ],
            [
                'name' => 'Arduino',
                'description' => 'Pemrograman mikrokontroler Arduino. Belajar elektronika dan IoT dengan platform open-source.',
                'price' => 2200000,
            ],
            [
                'name' => 'AI Generatif (1 Pertemuan)',
                'description' => 'Workshop intensif tentang AI Generatif, ChatGPT, dan tools AI modern dalam satu pertemuan.',
                'price' => 500000,
            ],
            [
                'name' => 'Wordpress x DIVI x AI (1 Pertemuan)',
                'description' => 'Workshop membuat website dengan WordPress, theme DIVI, dan integrasi AI dalam satu pertemuan.',
                'price' => 750000,
            ],
            [
                'name' => 'Python',
                'description' => 'Pemrograman Python dari dasar hingga mahir. Cocok untuk data science, web development, dan automation.',
                'price' => 3000000,
            ],
            [
                'name' => 'Laravel',
                'description' => 'Framework PHP Laravel untuk web development. Belajar membuat aplikasi web modern dan scalable.',
                'price' => 3500000,
            ],
            [
                'name' => 'Javascript',
                'description' => 'Pemrograman JavaScript modern (ES6+). Dari fundamental hingga framework seperti React dan Node.js.',
                'price' => 3200000,
            ],
            [
                'name' => 'Minecraft EDU',
                'description' => 'Belajar programming dan computational thinking melalui Minecraft Education Edition.',
                'price' => 1600000,
            ],
            [
                'name' => 'Roblox',
                'description' => 'Game development menggunakan Roblox Studio dan Lua scripting. Buat game sendiri di platform Roblox.',
                'price' => 2000000,
            ],
            [
                'name' => 'Unity 2D',
                'description' => 'Game development 2D menggunakan Unity Engine dan C#. Belajar membuat game dari konsep hingga publish.',
                'price' => 2800000,
            ],
        ];

        foreach ($courses as $courseData) {
            Course::updateOrCreate(
                ['name' => $courseData['name']],
                $courseData
            );
        }

        $this->command->info('Courses seeded successfully!');
    }
}