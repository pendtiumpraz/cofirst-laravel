<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassName;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
        
        $classes = ClassName::where('teacher_id', $user->id)
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
        
        // Verify teacher owns this class
        if ($class->teacher_id !== $user->id) {
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
        $students = User::whereHas('enrollments.class', function($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })->with(['enrollments.class.course'])->get();
        
        return view('teacher.students', compact('students'));
    }
}
