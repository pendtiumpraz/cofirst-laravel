<?php

namespace Database\Seeders;

use App\Models\ClassName;
use App\Models\Schedule;
use App\Models\Enrollment;
use App\Models\TeacherAssignment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = ClassName::all();
        $enrollments = Enrollment::all();
        $teacherAssignments = TeacherAssignment::all();

        if ($enrollments->isEmpty()) {
            $this->command->info('Tidak ada enrollment yang ditemukan, lewati ScheduleSeeder.');
            return;
        }

        if ($teacherAssignments->isEmpty()) {
            $this->command->info('Tidak ada teacher assignment yang ditemukan, lewati ScheduleSeeder.');
            return;
        }

        foreach ($classes as $class) {
            // Buat 5 jadwal untuk setiap kelas
            for ($i = 1; $i <= 5; $i++) {
                $date = Carbon::now()->addDays($i * 2);
                Schedule::factory()->create([
                    'class_id' => $class->id,
                    'schedule_date' => $date->format('Y-m-d'),
                    'schedule_time' => $date->format('H:i:s'),
                    'enrollment_id' => $enrollments->random()->id, // Ambil enrollment secara acak
                    'teacher_assignment_id' => $teacherAssignments->random()->id, // Ambil teacher assignment secara acak
                    'is_active' => true,
                ]);
            }
        }
    }
}