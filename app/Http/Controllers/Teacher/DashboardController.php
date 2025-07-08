<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\ClassName;
use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:teacher');
    }

    /**
     * Display the teacher dashboard.
     */
    public function index()
    {
        $teacher = Auth::user();
        
        // Get teacher's classes through teacher assignments
        $teacherClasses = ClassName::whereHas('schedules.teacherAssignment', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->with(['course', 'schedules' => function($query) use ($teacher) {
            $query->whereHas('teacherAssignment', function($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id);
            });
        }])->get();
        
        // Get today's schedules (based on day of week since schedules are weekly)
        $todayDayOfWeek = Carbon::today()->dayOfWeek;
        if ($todayDayOfWeek == 0) $todayDayOfWeek = 7; // Convert Sunday from 0 to 7
        
        $todaySchedules = Schedule::where('day_of_week', $todayDayOfWeek)
            ->whereHas('teacherAssignment', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->with(['className.course', 'enrollment.student'])
            ->orderBy('start_time')
            ->get();
        
        // Get all weekly schedules for this teacher
        $upcomingSchedules = Schedule::whereHas('teacherAssignment', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->with(['className.course', 'enrollment.student'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        
        // Get total students taught by this teacher
        $totalStudents = User::role('student')
            ->whereHas('enrollments.className.schedules.teacherAssignment', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->distinct()
            ->count();
        
        // Get recent reports created by this teacher
        $recentReports = Report::where('teacher_id', $teacher->id)
            ->with(['student', 'className'])
            ->latest()
            ->take(5)
            ->get();
        
        // Statistics
        $stats = [
            'total_classes' => $teacherClasses->count(),
            'total_students' => $totalStudents,
            'today_schedules' => $todaySchedules->count(),
            'total_reports' => Report::where('teacher_id', $teacher->id)->count(),
        ];
        
        return view('teacher.dashboard', compact(
            'teacherClasses',
            'todaySchedules', 
            'upcomingSchedules',
            'recentReports',
            'stats'
        ));
    }
}