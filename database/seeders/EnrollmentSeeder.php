<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\ClassName;
use Carbon\Carbon;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active students
        $students = User::role('student')->where('is_active', true)->get();
        
        // Get all active classes
        $classes = ClassName::where('status', 'active')->get();
        
        if ($students->isEmpty() || $classes->isEmpty()) {
            echo "No students or classes found. Skipping enrollment seeding.\n";
            return;
        }
        
        // Create enrollments for existing students in existing classes
        foreach ($students as $student) {
            // Each student can be enrolled in classes, but limited by available classes
            $maxEnrollments = min(3, $classes->count());
            $enrollmentCount = rand(1, $maxEnrollments);
            $classesToEnroll = $classes->random($enrollmentCount);
            
            foreach ($classesToEnroll as $class) {
                // Check if enrollment already exists
                $existingEnrollment = Enrollment::where('student_id', $student->id)
                    ->where('class_id', $class->id)
                    ->first();
                
                if (!$existingEnrollment) {
                    Enrollment::create([
                        'student_id' => $student->id,
                        'class_id' => $class->id,
                        'enrollment_date' => Carbon::now()->subDays(rand(1, 30)),
                        'status' => collect(['active', 'completed', 'suspended'])->random(),
                    ]);
                }
            }
        }
        
        // Create additional dummy enrollments if we don't have enough
        $currentCount = Enrollment::count();
        $targetCount = 25;
        
        if ($currentCount < $targetCount) {
            $additionalNeeded = $targetCount - $currentCount;
            
            for ($i = 0; $i < $additionalNeeded; $i++) {
                $randomStudent = $students->random();
                $randomClass = $classes->random();
                
                // Check if enrollment already exists
                $existingEnrollment = Enrollment::where('student_id', $randomStudent->id)
                    ->where('class_id', $randomClass->id)
                    ->first();
                
                if (!$existingEnrollment) {
                    Enrollment::create([
                        'student_id' => $randomStudent->id,
                        'class_id' => $randomClass->id,
                        'enrollment_date' => Carbon::now()->subDays(rand(1, 60)),
                        'status' => collect(['active', 'completed', 'suspended'])->random(),
                    ]);
                }
            }
        }
        
        echo "Created " . Enrollment::count() . " enrollments total.\n";
    }
}
