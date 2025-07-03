<?php

namespace Database\Seeders;

use App\Models\ClassName;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClassNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();
        $teachers = User::role('teacher')->get();
        
        if ($courses->isEmpty()) {
            $this->command->info('Tidak ada course yang ditemukan, lewati ClassNameSeeder.');
            return;
        }
        
        if ($teachers->isEmpty()) {
            $this->command->info('Tidak ada guru yang ditemukan, lewati ClassNameSeeder.');
            return;
        }

        // Daftar kelas yang akan dibuat untuk setiap course
        $classTypes = [
            [
                'suffix' => 'Group Class (Online)',
                'type' => 'group_class_3_5_kids',
                'delivery_method' => 'online'
            ],
            [
                'suffix' => 'Private Home Call (Offline)',
                'type' => 'private_home_call', 
                'delivery_method' => 'offline'
            ],
            [
                'suffix' => 'Online Webinar',
                'type' => 'online_webinar',
                'delivery_method' => 'online'
            ]
        ];

        foreach ($courses as $course) {
            // Cari kurikulum yang sesuai dengan course
            $curriculum = Curriculum::where('course_id', $course->id)->first();
            
            if (!$curriculum) {
                $this->command->warn("Kurikulum tidak ditemukan untuk course: {$course->name}");
                continue;
            }

            foreach ($classTypes as $classType) {
                $className = $course->name . ' - ' . $classType['suffix'];
                
                ClassName::updateOrCreate(
                    ['name' => $className],
                    [
                        'course_id' => $course->id,
                        'teacher_id' => $teachers->random()->id,
                        'name' => $className,
                        'type' => $classType['type'],
                        'delivery_method' => $classType['delivery_method'],
                        'curriculum_id' => $curriculum->id,
                        'description' => 'Kelas ' . $course->name . ' dengan metode ' . $classType['suffix'],
                        'start_date' => now(),
                        'end_date' => now()->addWeeks($curriculum->duration_weeks ?? 12),
                        'max_students' => $this->getMaxStudents($classType['type']),
                        'status' => 'planned',
                        'is_active' => true
                    ]
                );
                
                $this->command->info("Created/Updated class: {$className}");
            }
        }
    }
    
    private function getMaxStudents($type)
    {
        switch ($type) {
            case 'group_class_3_5_kids':
                return 5;
            case 'private_home_call':
                return 1;
            case 'online_webinar':
                return 50;
            default:
                return 10;
        }
    }
}