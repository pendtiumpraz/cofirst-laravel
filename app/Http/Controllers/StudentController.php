<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Report;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class StudentController extends Controller
{
    /**
     * Display student's classes.
     */
    public function classes()
    {
        $user = Auth::user();
        $enrollments = $user->enrollments()
            ->with(['className.course', 'className.teachers'])
            ->get();
            
        // Debug logging
        \Log::info('Student Classes Debug', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_roles' => $user->roles->pluck('name'),
            'enrollments_count' => $enrollments->count(),
            'first_enrollment' => $enrollments->first() ? [
                'id' => $enrollments->first()->id,
                'status' => $enrollments->first()->status,
                'class_name' => $enrollments->first()->className?->name,
                'course_name' => $enrollments->first()->className?->course?->name,
                'teacher_name' => $enrollments->first()->className?->teachers->first()?->name,
            ] : null
        ]);
            
        return view('student.classes.index', compact('enrollments'));
    }

    /**
     * Show a specific class for the student.
     */
    public function showClass($classId)
    {
        $user = Auth::user();
        
        // Verify the student is enrolled in this class
        $enrollment = $user->enrollments()
            ->where('class_id', $classId)
            ->with(['className.course', 'className.teachers'])
            ->first();
            
        if (!$enrollment) {
            abort(404, 'Class not found or you are not enrolled in this class.');
        }
        
        // Get schedules for this class
        $schedules = Schedule::whereHas('enrollment', function ($query) use ($user, $classId) {
                $query->where('student_id', $user->id)
                      ->where('class_id', $classId);
            })
            ->with(['teacherAssignment.teacher'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
            
        // Get reports for this class
        $reports = Report::where('student_id', $user->id)
            ->whereHas('className', function($query) use ($classId) {
                $query->where('id', $classId);
            })
            ->with(['teacher'])
            ->latest()
            ->take(5)
            ->get();
        
        return view('student.classes.show', compact('enrollment', 'schedules', 'reports'));
    }

    /**
     * Display student's schedule.
     */
    public function schedule()
    {
        $user = Auth::user();
        $schedules = Schedule::whereHas('enrollment', function ($query) use ($user) {
                $query->where('student_id', $user->id);
            })
            ->with(['className.course', 'className.teachers', 'teacherAssignment.teacher'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        
        return view('student.schedules.index', compact('schedules'));
    }

    /**
     * Display student's reports.
     */
    public function reports()
    {
        $user = Auth::user();
        $reports = Report::where('student_id', $user->id)
            ->with(['teacher', 'className.course'])
            ->latest()
            ->paginate(10);
            
        return view('student.reports.index', compact('reports'));
    }

    /**
     * Display student's payments.
     */
    public function payments()
    {
        $user = Auth::user();
        $transactions = FinancialTransaction::where('student_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('student.payments', compact('transactions'));
    }

    /**
     * Display available courses for student.
     */
    public function courses()
    {
        $user = Auth::user();
        
        // Get all available courses
        $courses = \App\Models\Course::where('status', 'active')
            ->with(['classes' => function($query) {
                $query->where('status', 'active');
            }])
            ->get();
        
        // Get student's enrolled courses
        $enrolledCourseIds = Enrollment::where('student_id', $user->id)
            ->where('status', 'active')
            ->join('class_names', 'enrollments.class_id', '=', 'class_names.id')
            ->pluck('class_names.course_id')
            ->toArray();
            
        return view('student.courses.index', compact('courses', 'enrolledCourseIds'));
    }

    /**
     * Display child's reports (for parents).
     */
    public function childReports($studentId)
    {
        $user = Auth::user();
        
        // Verify parent-child relationship
        if (!$user->children()->where('student_id', $studentId)->exists()) {
            abort(403, 'Unauthorized access to student data.');
        }
        
        $student = User::findOrFail($studentId);
        $reports = Report::where('student_id', $studentId)
            ->with(['teacher', 'className.course'])
            ->latest()
            ->paginate(10);
            
        return view('parent.child-reports', compact('student', 'reports'));
    }

    /**
     * Display child's schedule (for parents).
     */
    public function childSchedule($studentId)
    {
        $user = Auth::user();
        
        // Verify parent-child relationship
        if (!$user->children()->where('student_id', $studentId)->exists()) {
            abort(403, 'Unauthorized access to student data.');
        }
        
        $student = User::findOrFail($studentId);
        $schedules = Schedule::whereHas('enrollment', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->with(['className.course', 'className.teachers', 'teacherAssignment.teacher'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        
        return view('parent.child-schedule', compact('student', 'schedules'));
    }

    /**
     * Display child's payments (for parents).
     */
    public function childPayments($studentId)
    {
        $user = Auth::user();
        
        // Verify parent-child relationship
        if (!$user->children()->where('student_id', $studentId)->exists()) {
            abort(403, 'Unauthorized access to student data.');
        }
        
        $student = User::findOrFail($studentId);
        $transactions = FinancialTransaction::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('parent.child-payments', compact('student', 'transactions'));
    }
}
