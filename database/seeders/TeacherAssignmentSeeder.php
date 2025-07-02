<?php

namespace Database\Seeders;

use App\Models\ClassName;
use App\Models\User;
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

        foreach ($classes as $class) {
            TeacherAssignment::factory()->create([
                'class_id' => $class->id, // Diperbaiki dari class_name_id
                'teacher_id' => $teachers->random()->id, // Ambil guru secara acak
            ]);
        }
    }
}