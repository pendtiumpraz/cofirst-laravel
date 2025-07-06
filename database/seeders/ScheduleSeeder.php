<?php

namespace Database\Seeders;

use App\Models\ClassName;
use App\Models\Schedule;
use App\Models\Course;
use App\Models\Curriculum;
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

        // Definisi jadwal berdasarkan tipe kelas
        $scheduleTemplates = [
            'Group Class' => [
                'days' => ['Monday', 'Wednesday', 'Friday'],
                'times' => [['09:00:00', '11:00:00'], ['13:00:00', '15:00:00'], ['16:00:00', '18:00:00']],
                'rooms' => ['Room A', 'Room B', 'Room C']
            ],
            'Private Home Call' => [
                'days' => ['Tuesday', 'Thursday', 'Saturday'],
                'times' => [['10:00:00', '12:00:00'], ['14:00:00', '16:00:00'], ['19:00:00', '21:00:00']],
                'rooms' => ['Home Visit']
            ],
            'Online Webinar' => [
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'times' => [['08:00:00', '10:00:00'], ['19:00:00', '21:00:00'], ['20:00:00', '22:00:00']],
                'rooms' => ['Online']
            ]
        ];

        foreach ($classes as $class) {
            $course = Course::find($class->course_id);
            $curriculum = Curriculum::where('course_id', $class->course_id)->first();
            
            if (!$course || !$curriculum) {
                $this->command->warn("Course atau Curriculum tidak ditemukan untuk class: {$class->name}");
                continue;
            }

            // Ambil teacher assignment dan enrollments untuk kelas ini
            $teacherAssignment = TeacherAssignment::where('class_id', $class->id)->first();
            $enrollments = Enrollment::where('class_id', $class->id)->where('status', 'active')->get();
            
            // Hanya buat jadwal jika ada teacher assignment dan enrollment
            if (!$teacherAssignment) {
                $this->command->warn("Kelas {$class->name} tidak memiliki teacher assignment, skip membuat jadwal");
                continue;
            }
            
            if ($enrollments->count() === 0) {
                $this->command->warn("Kelas {$class->name} tidak memiliki enrollment aktif, skip membuat jadwal");
                continue;
            }

            // Tentukan template jadwal berdasarkan nama kelas
            $classType = 'Group Class'; // default
            if (str_contains($class->name, 'Private')) {
                $classType = 'Private Home Call';
            } elseif (str_contains($class->name, 'Online')) {
                $classType = 'Online Webinar';
            }

            $template = $scheduleTemplates[$classType];
            $totalWeeks = $curriculum->duration_weeks ?? 12;
            
            // Pilih hari dan waktu yang konsisten untuk kelas ini
            $selectedDay = collect($template['days'])->random();
            $selectedTime = collect($template['times'])->random();
            $selectedRoom = collect($template['rooms'])->random();

            // Buat jadwal mingguan (satu jadwal per kelas)
            $dayNumber = $this->getDayNumber($selectedDay);
            
            // Buat jadwal untuk setiap enrollment (siswa) di kelas ini
            foreach ($enrollments as $enrollment) {
                Schedule::updateOrCreate(
                    [
                        'class_id' => $class->id,
                        'enrollment_id' => $enrollment->id,
                        'teacher_assignment_id' => $teacherAssignment->id,
                    ],
                    [
                        'day_of_week' => $dayNumber,
                        'start_time' => $selectedTime[0],
                        'end_time' => $selectedTime[1],
                        'room' => $selectedRoom,
                        'is_active' => true
                    ]
                );
            }
            $this->command->info("Jadwal dibuat untuk kelas: {$class->name} ({$classType}) - {$selectedDay} {$selectedTime[0]}-{$selectedTime[1]} dengan {$enrollments->count()} siswa dan teacher: {$teacherAssignment->teacher->name}");
        }
        
        // Create additional dummy schedules if we don't have enough
        $currentCount = Schedule::count();
        $targetCount = 25;
        
        if ($currentCount < $targetCount) {
            $additionalNeeded = $targetCount - $currentCount;
            $this->command->info("Creating {$additionalNeeded} additional schedules for pagination testing...");
            
            // Get available data for creating dummy schedules
            $availableClasses = ClassName::where('status', 'active')->get();
            $availableEnrollments = Enrollment::where('status', 'active')->get();
            $availableTeacherAssignments = TeacherAssignment::all();
            
            if ($availableClasses->isNotEmpty() && $availableEnrollments->isNotEmpty() && $availableTeacherAssignments->isNotEmpty()) {
                for ($i = 0; $i < $additionalNeeded; $i++) {
                    $randomClass = $availableClasses->random();
                    $randomEnrollment = $availableEnrollments->random();
                    $randomTeacherAssignment = $availableTeacherAssignments->random();
                    
                    // Random schedule data
                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    $times = [
                        ['08:00:00', '10:00:00'],
                        ['10:00:00', '12:00:00'],
                        ['13:00:00', '15:00:00'],
                        ['15:00:00', '17:00:00'],
                        ['19:00:00', '21:00:00']
                    ];
                    $rooms = ['Room A', 'Room B', 'Room C', 'Room D', 'Online', 'Home Visit'];
                    
                    $selectedDay = collect($days)->random();
                    $selectedTime = collect($times)->random();
                    $selectedRoom = collect($rooms)->random();
                    
                    // Check if schedule already exists
                    $existingSchedule = Schedule::where('class_id', $randomClass->id)
                        ->where('enrollment_id', $randomEnrollment->id)
                        ->where('teacher_assignment_id', $randomTeacherAssignment->id)
                        ->where('day_of_week', $this->getDayNumber($selectedDay))
                        ->where('start_time', $selectedTime[0])
                        ->first();
                    
                    if (!$existingSchedule) {
                        Schedule::create([
                            'class_id' => $randomClass->id,
                            'enrollment_id' => $randomEnrollment->id,
                            'teacher_assignment_id' => $randomTeacherAssignment->id,
                            'day_of_week' => $this->getDayNumber($selectedDay),
                            'start_time' => $selectedTime[0],
                            'end_time' => $selectedTime[1],
                            'room' => $selectedRoom,
                            'is_active' => true
                        ]);
                    }
                }
            }
        }
        
        $this->command->info("Total schedules created: " . Schedule::count());
    }

    /**
     * Konversi nama hari ke nomor hari (1=Monday, 7=Sunday)
     */
    private function getDayNumber($dayName): int
    {
        $days = [
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
            'Sunday' => 7
        ];

        return $days[$dayName] ?? 1;
    }
}