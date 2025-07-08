<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:student']);
    }

    /**
     * Display student dashboard with purchased and available classes
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get student's enrolled classes (purchased classes)
        $enrollments = Enrollment::where('student_id', $user->id)
            ->where('status', 'active')
            ->with(['class.course'])
            ->get();

        // Get enrolled class IDs
        $enrolledClassIds = $enrollments->pluck('class_id')->toArray();
        
        // Get available classes (active classes that student is not enrolled in)
        $availableClasses = ClassName::with(['course', 'teachers'])
            ->where('is_active', true)
            ->whereIn('status', ['planned', 'active'])
            ->withCount('enrollments')
            ->get();

        // Get student's schedules for calendar
        $schedules = Schedule::forCalendar()
            ->with(['className.course', 'teacherAssignment.teacher', 'enrollment.student'])
            ->whereHas('enrollment', function ($query) use ($user) {
                $query->where('student_id', $user->id)
                      ->where('status', 'active');
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Get today's schedule
        $today = Carbon::today();
        $todayDayOfWeek = $today->dayOfWeek === 0 ? 7 : $today->dayOfWeek; // Convert Sunday from 0 to 7
        $todaySchedules = $schedules->filter(function ($schedule) use ($todayDayOfWeek) {
            return $schedule->day_of_week == $todayDayOfWeek;
        })->sortBy('start_time');

        // Calculate summary statistics
        $stats = [
            'enrolled_courses' => $enrollments->count(),
            'classes_today' => $todaySchedules->count(),
            'attendance_rate' => 95, // Placeholder - implement actual attendance calculation
            'available_classes' => $availableClasses->count()
        ];

        // Add missing variables for the view
        $myCourses = $enrollments; // Same as enrollments
        $announcements = collect(); // Empty for now, can be implemented later
        
        return view('student.dashboard', compact('enrollments', 'availableClasses', 'schedules', 'todaySchedules', 'stats', 'myCourses', 'announcements'));
    }
}