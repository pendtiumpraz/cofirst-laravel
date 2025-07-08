<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassName;
use App\Models\Report;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TeacherController extends Controller
{
    /**
     * Display teacher's classes.
     */
    public function classes()
    {
        $user = Auth::user();
        
        // Debug info
        \Log::info('Teacher Classes Debug', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_roles' => $user->roles->pluck('name'),
        ]);
        
        $classes = ClassName::whereHas('teachers', function($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with(['course', 'enrollments.student'])
            ->withCount('enrollments')
            ->where('status', 'active')
            ->get();
            
        $totalStudents = $classes->sum('enrollments_count');
        
        \Log::info('Teacher Classes Query Result', [
            'classes_count' => $classes->count(),
            'total_students' => $totalStudents,
        ]);
            
        return view('teacher.classes.index', compact('classes', 'totalStudents'));
    }

    /**
     * Display class detail for teacher.
     */
    public function classDetail(ClassName $class)
    {
        $user = Auth::user();
        
        // Verify teacher is assigned to this class
        if (!$class->teachers->contains($user->id)) {
            abort(403, 'Unauthorized access to class data.');
        }
        
        $class->load(['course', 'enrollments.student', 'schedules']);
        
        return view('teacher.class-detail', compact('class'));
    }

    /**
     * Display teacher's students.
     */
    public function students()
    {
        $user = Auth::user();
        $students = User::whereHas('enrollments.class.teachers', function($query) use ($user) {
            $query->where('users.id', $user->id);
        })->with(['enrollments.class.course'])->get();
        
        return view('teacher.students', compact('students'));
    }

    /**
     * Display teacher ranking by student count.
     */
    public function ranking()
    {
        $teachers = User::role('teacher')
            ->withCount(['teachingClasses as student_count' => function ($query) {
                $query->select(DB::raw('count(distinct enrollments.student_id)'))
                    ->join('enrollments', 'enrollments.class_id', '=', 'class_names.id');
            }])
            ->orderBy('student_count', 'desc')
            ->get();

        return view('admin.teachers.ranking', compact('teachers'));
    }

    /**
     * Display teacher's schedule.
     */
    public function schedule()
    {
        $user = Auth::user();
        
        // Get teacher's schedules
        $schedules = Schedule::forCalendar()
            ->whereHas('teacherAssignment', function($query) use ($user) {
                $query->where('teacher_id', $user->id);
            })
            ->with(['className.course', 'teacherAssignment.teacher', 'enrollment.student'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Get today's schedules
        $today = Carbon::today();
        $todayDayOfWeek = $today->dayOfWeek === 0 ? 7 : $today->dayOfWeek;
        $todaySchedules = $schedules->filter(function ($schedule) use ($todayDayOfWeek) {
            return $schedule->day_of_week == $todayDayOfWeek;
        })->sortBy('start_time');

        return view('teacher.schedules.index', compact('schedules', 'todaySchedules'));
    }

    /**
     * Display teacher's attendance management.
     */
    public function attendance()
    {
        $user = Auth::user();
        
        // Get teacher's schedules with attendance data
        $schedules = Schedule::forCalendar()
            ->whereHas('teacherAssignment', function($query) use ($user) {
                $query->where('teacher_id', $user->id);
            })
            ->with(['className.course', 'teacherAssignment.teacher', 'enrollment.student'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('teacher.attendance.index', compact('schedules'));
    }

    /**
     * Display today's attendance.
     */
    public function attendanceToday()
    {
        $user = Auth::user();
        
        // Get today's schedules
        $today = Carbon::today();
        $todayDayOfWeek = $today->dayOfWeek === 0 ? 7 : $today->dayOfWeek;
        
        $todaySchedules = Schedule::forCalendar()
            ->whereHas('teacherAssignment', function($query) use ($user) {
                $query->where('teacher_id', $user->id);
            })
            ->where('day_of_week', $todayDayOfWeek)
            ->with(['className.course', 'teacherAssignment.teacher', 'enrollment.student'])
            ->orderBy('start_time')
            ->get();

        return view('teacher.attendance.today', compact('todaySchedules'));
    }

    /**
     * Display teacher's handovers.
     */
    public function handovers()
    {
        $user = Auth::user();
        
        // Get teacher's handovers (placeholder)
        $handovers = collect([]); // Empty for now, can be implemented later
        
        return view('teacher.handovers.index', compact('handovers'));
    }
}
