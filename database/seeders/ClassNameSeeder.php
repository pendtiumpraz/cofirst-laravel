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
            // Buat kelas Group jika belum ada
            $groupClassName = $course->name . ' - Group Class (Online)';
            if (!ClassName::where('name', $groupClassName)->exists()) {
                ClassName::factory()->create([
                    'course_id' => $course->id,
                    'name' => $groupClassName,
                    'type' => 'group_class_3_5_kids',
                    'delivery_method' => 'online',
                    'curriculum_id' => $curriculums->random()->id,
                ]);
            }

            // Buat kelas Private jika belum ada
            $privateClassName = $course->name . ' - Private Home Call (Offline)';
            if (!ClassName::where('name', $privateClassName)->exists()) {
                ClassName::factory()->create([
                    'course_id' => $course->id,
                    'name' => $privateClassName,
                    'type' => 'private_home_call',
                    'delivery_method' => 'offline',
                    'curriculum_id' => $curriculums->random()->id,
                ]);
            }

            // Buat kelas Webinar jika belum ada
            $webinarClassName = $course->name . ' - Online Webinar';
            if (!ClassName::where('name', $webinarClassName)->exists()) {
                ClassName::factory()->create([
                    'course_id' => $course->id,
                    'name' => $webinarClassName,
                    'type' => 'online_webinar',
                    'delivery_method' => 'online',
                    'curriculum_id' => $curriculums->random()->id,
                ]);
            }
        }
    }
}