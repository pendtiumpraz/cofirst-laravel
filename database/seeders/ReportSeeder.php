<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enrollments = Enrollment::with('student', 'class')->get();

        foreach ($enrollments as $enrollment) {
            $classId = $enrollment->class->id;
            $studentId = $enrollment->student_id;
            $teacherId = $enrollment->class->teacher_id;

            if ($classId && $studentId && $teacherId) {
                Report::factory()->create([
                    'class_id' => $classId,
                    'student_id' => $studentId,
                    'teacher_id' => $teacherId,
                    'title' => 'Laporan Kemajuan Awal',
                    'content' => 'Siswa menunjukkan kemajuan yang baik di minggu pertama.',
                    'report_date' => now(),
                    'type' => 'weekly',
                    'progress_percentage' => rand(50, 100),
                    'recommendations' => 'Terus tingkatkan partisipasi di kelas.',
                ]);
            }
        }
    }
}
