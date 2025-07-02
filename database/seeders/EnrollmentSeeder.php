<?php

namespace Database\Seeders;

use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = ClassName::all();
        $students = User::role('student')->get();

        if ($students->isEmpty()) {
            $this->command->info('Tidak ada siswa yang ditemukan, lewati EnrollmentSeeder.');
            return;
        }

        // Daftarkan setiap siswa ke salah satu kelas secara acak
        foreach ($students as $student) {
            $classToEnroll = $classes->random();
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'class_id' => $classToEnroll->id, // Diperbaiki dari class_name_id
                'enrollment_date' => now(),
                'status' => 'active', // Status bisa 'active', 'completed', 'dropped'
            ]);
        }
    }
}
