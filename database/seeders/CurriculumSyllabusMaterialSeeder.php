<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Syllabus;
use App\Models\Material;
use Illuminate\Database\Seeder;

class CurriculumSyllabusMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            // Setiap course punya 1 curriculum
            $curriculum = Curriculum::factory()->create([
                'course_id' => $course->id,
                'title' => 'Kurikulum Lengkap ' . $course->name,
            ]);

            // Setiap curriculum punya 5 syllabus
            for ($i = 1; $i <= 5; $i++) {
                $syllabus = Syllabus::factory()->create([
                    'curriculum_id' => $curriculum->id,
                    'title' => "Modul {$i}: Pengenalan Topik {$i}",
                ]);

                // Setiap syllabus punya 3 materi
                for ($j = 1; $j <= 3; $j++) {
                    Material::factory()->create([
                        'syllabus_id' => $syllabus->id,
                        'title' => "Materi {$j} - " . $syllabus->title,
                        'content' => 'Ini adalah konten untuk materi ' . $j,
                    ]);
                }
            }
        }
    }
}
