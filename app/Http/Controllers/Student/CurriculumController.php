<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\Syllabus;
use App\Models\Material;
use App\Models\Enrollment;
use App\Models\ClassName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurriculumController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:student']);
    }

    /**
     * Display student's enrolled classes with curriculum info
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get student's active enrollments with class and curriculum info
        $enrollments = Enrollment::where('student_id', $user->id)
            ->where('status', 'active')
            ->with([
                'class.course',
                'class.curriculum.syllabuses.materials',
                'class.teacher'
            ])
            ->get();

        return view('student.curriculum.index', compact('enrollments'));
    }

    /**
     * Show curriculum details for a specific class
     */
    public function show($classId)
    {
        $user = Auth::user();
        
        // Verify student is enrolled in this class
        $enrollment = Enrollment::where('student_id', $user->id)
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->first();
            
        if (!$enrollment) {
            abort(403, 'You are not enrolled in this class.');
        }
        
        $class = ClassName::with([
            'course',
            'curriculum.syllabuses' => function($query) {
                $query->where('status', 'active')->orderBy('meeting_number');
            },
            'curriculum.syllabuses.materials' => function($query) {
                $query->where('status', 'active')->orderBy('order');
            },
            'teacher'
        ])->findOrFail($classId);
        
        // Get student's material progress (you might need to create a progress tracking system)
        $studentProgress = $this->getStudentProgress($user->id, $class->curriculum->id);
        
        return view('student.curriculum.show', compact('class', 'studentProgress'));
    }
    
    /**
     * Get student's progress for materials in a curriculum
     * This is a placeholder - you might want to implement a proper progress tracking system
     */
    private function getStudentProgress($studentId, $curriculumId)
    {
        // For now, return empty array
        // In a real implementation, you would track which materials have been:
        // - viewed/studied
        // - completed
        // - in progress
        
        return [
            'completed' => [], // material IDs that are completed
            'in_progress' => [], // material IDs that are in progress
            'not_started' => [] // material IDs that haven't been started
        ];
    }
}