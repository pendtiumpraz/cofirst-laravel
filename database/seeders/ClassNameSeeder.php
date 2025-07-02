<?php

namespace Database\Seeders;

use App\Models\ClassName;
use App\Models\Course;
use App\Models\Curriculum;
use Illuminate\Database\Seeder;

class ClassNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();
        $curriculums = Curriculum::all();

        if ($curriculums->isEmpty()) {
            $this->command->info('Tidak ada kurikulum yang ditemukan, lewati ClassNameSeeder.');
            return;
        }

        foreach ($courses as $course) {
            // Buat kelas Group
            ClassName::factory()->create([
                'course_id' => $course->id,
                'name' => $course->name . ' - Group Class (Online)',
                'type' => 'group_class_3_5_kids',
                'delivery_method' => 'online',
                'curriculum_id' => $curriculums->random()->id,
            ]);

            // Buat kelas Private
            ClassName::factory()->create([
                'course_id' => $course->id,
                'name' => $course->name . ' - Private Home Call (Offline)',
                'type' => 'private_home_call',
                'delivery_method' => 'offline',
                'curriculum_id' => $curriculums->random()->id,
            ]);

            // Buat kelas Webinar
            ClassName::factory()->create([
                'course_id' => $course->id,
                'name' => $course->name . ' - Online Webinar',
                'type' => 'online_webinar',
                'delivery_method' => 'online',
                'curriculum_id' => $curriculums->random()->id,
            ]);
        }
    }
}