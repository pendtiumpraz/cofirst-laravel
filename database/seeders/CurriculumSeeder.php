<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Curriculum;
use App\Models\Course;

class CurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have courses
        $courses = Course::where('is_active', true)->get();
        
        if ($courses->isEmpty()) {
            // Create some dummy courses if none exist
            $courses = collect([
                Course::create([
                    'name' => 'Basic Programming',
                    'description' => 'Learn the fundamentals of programming',
                    'level' => 'Beginner',
                    'duration_hours' => 40,
                    'is_active' => true,
                ]),
                Course::create([
                    'name' => 'Web Development',
                    'description' => 'Build modern web applications',
                    'level' => 'Intermediate',
                    'duration_hours' => 60,
                    'is_active' => true,
                ]),
                Course::create([
                    'name' => 'Advanced Programming',
                    'description' => 'Master advanced programming concepts',
                    'level' => 'Advanced',
                    'duration_hours' => 80,
                    'is_active' => true,
                ]),
            ]);
        }
        
        $types = ['fast-track', 'regular', 'expert', 'beginner'];
        $statuses = ['active', 'inactive'];
        
        // Create 25 dummy curriculums to test pagination (limit is 15)
        for ($i = 1; $i <= 25; $i++) {
            $course = $courses->random();
            $type = $types[array_rand($types)];
            $status = $statuses[array_rand($statuses)];
            
            Curriculum::create([
                'course_id' => $course->id,
                'title' => "Curriculum {$i} for {$course->name}",
                'description' => "This is a comprehensive curriculum designed for {$course->name}. It covers all essential topics and practical applications needed to master the subject.",
                'type' => $type,
                'status' => $status,
                'duration_weeks' => rand(4, 16),
                'objectives' => "By the end of this curriculum, students will be able to:\n- Understand core concepts\n- Apply knowledge practically\n- Build real-world projects\n- Demonstrate proficiency",
            ]);
        }
    }
}
