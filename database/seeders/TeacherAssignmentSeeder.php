<?php

namespace Database\Seeders;

use App\Models\ClassName;
use App\Models\User;
use App\Models\Course;
use App\Models\TeacherAssignment;
use Illuminate\Database\Seeder;

class TeacherAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = ClassName::all();
        $teachers = User::role('teacher')->get();

        if ($teachers->isEmpty()) {
            $this->command->info('Tidak ada guru yang ditemukan, lewati TeacherAssignmentSeeder.');
            return;
        }

        if ($classes->isEmpty()) {
            $this->command->info('Tidak ada kelas yang ditemukan, lewati TeacherAssignmentSeeder.');
            return;
        }

        // Mapping guru berdasarkan keahlian/spesialisasi
        $teacherSpecialization = [
            'Scratch' => ['Dika', 'Lazu', 'Ghaza'],
            'Lego Spike (Robotik)' => ['Galih', 'Fajrul', 'Diyan'],
            'Microbit' => ['Rizca', 'Joan', 'Hafidz'],
            'Arduino' => ['Savero', 'Unggul', 'Haritz'],
            'AI Generatif (1 Pertemuan)' => ['Muslimin', 'Dika', 'Lazu'],
            'Wordpress x DIVI x AI (1 Pertemuan)' => ['Ghaza', 'Galih', 'Fajrul'],
            'Python' => ['Diyan', 'Rizca', 'Joan'],
            'Laravel' => ['Hafidz', 'Savero', 'Unggul'],
            'Javascript' => ['Haritz', 'Muslimin', 'Dika'],
            'Minecraft EDU' => ['Lazu', 'Ghaza', 'Galih'],
            'Roblox' => ['Fajrul', 'Diyan', 'Rizca'],
            'Unity 2D' => ['Joan', 'Hafidz', 'Savero']
        ];

        foreach ($classes as $class) {
            $course = Course::find($class->course_id);
            
            if (!$course) {
                $this->command->warn("Course tidak ditemukan untuk class: {$class->name}");
                continue;
            }

            // Cari guru yang sesuai dengan spesialisasi
            $specializedTeachers = $teacherSpecialization[$course->name] ?? [];
            
            if (!empty($specializedTeachers)) {
                // Pilih guru dari yang memiliki spesialisasi
                $teacherName = collect($specializedTeachers)->random();
                $teacher = $teachers->where('name', $teacherName)->first();
                
                if (!$teacher) {
                    // Fallback ke guru random jika tidak ditemukan
                    $teacher = $teachers->random();
                    $this->command->warn("Guru spesialis {$teacherName} tidak ditemukan untuk course {$course->name}, menggunakan guru random: {$teacher->name}");
                }
            } else {
                // Jika tidak ada spesialisasi, pilih guru secara acak
                $teacher = $teachers->random();
            }

            TeacherAssignment::updateOrCreate(
                [
                    'class_id' => $class->id,
                    'teacher_id' => $teacher->id
                ],
                [
                    'assigned_at' => now()
                ]
            );
            
            $this->command->info("Assigned teacher {$teacher->name} to class: {$class->name}");
        }
    }
}