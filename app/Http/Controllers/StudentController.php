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
        $enrollments = Enrollment::where('student_id', $user->id)
            ->with(['class.course', 'class.teacher'])
            ->where('status', 'active')
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
                'class_name' => $enrollments->first()->class?->name,
                'course_name' => $enrollments->first()->class?->course?->name,
                'teacher_name' => $enrollments->first()->class?->teacher?->name,
            ] : null
        ]);
            
        return view('student.classes.index', compact('enrollments'));
    }

    /**
     * Display student's schedule.
     */
    public function schedule()
    {
        $user = Auth::user();
        $schedules = Schedule::whereHas('class.enrollments', function($query) use ($user) {
            $query->where('student_id', $user->id)->where('status', 'active');
        })->with(['class.course', 'class.teacher'])->get();
        
        return view('student.schedules.index', compact('schedules'));
    }

    /**
     * Display student's reports.
     */
    public function reports()
    {
        $user = Auth::user();
        $reports = Report::where('student_id', $user->id)
            ->with(['teacher', 'class.course'])
            ->orderBy('created_at', 'desc')
            ->get();
            
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
            ->with(['teacher', 'class.course'])
            ->orderBy('created_at', 'desc')
            ->get();
            
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
        $schedules = Schedule::whereHas('class.enrollments', function($query) use ($studentId) {
            $query->where('student_id', $studentId)->where('status', 'active');
        })->with(['class.course', 'class.teacher'])->get();
        
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
