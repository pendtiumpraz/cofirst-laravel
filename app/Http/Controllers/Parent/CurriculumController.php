<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\ClassName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurriculumController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:parent']);
    }

    /**
     * Display parent's children and their enrolled classes
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get parent's children with their enrollments
        $children = $user->children()->with([
            'enrollments' => function($query) {
                $query->where('status', 'active');
            },
            'enrollments.class.course',
            'enrollments.class.curriculum',
            'enrollments.className.teachers'
        ])->get();

        return view('parent.curriculum.index', compact('children'));
    }

    /**
     * Show curriculum progress for a specific child's class
     */
    public function showChildProgress($childId, $classId)
    {
        $user = Auth::user();
        
        // Verify parent-child relationship
        if (!$user->children()->where('student_id', $childId)->exists()) {
            abort(403, 'Unauthorized access to student data.');
        }
        
        // Verify child is enrolled in this class
        $enrollment = Enrollment::where('student_id', $childId)
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->first();
            
        if (!$enrollment) {
            abort(404, 'Child is not enrolled in this class.');
        }
        
        $child = User::findOrFail($childId);
        $class = ClassName::with([
            'course',
            'curriculum.syllabuses' => function($query) {
                $query->where('status', 'active')->orderBy('order');
            },
            'curriculum.syllabuses.materials' => function($query) {
                $query->where('status', 'active')->orderBy('order');
            },
            'teacher'
        ])->findOrFail($classId);
        
        // Get child's material progress
        $childProgress = $this->getChildProgress($childId, $class->curriculum->id);
        
        return view('parent.curriculum.child-progress', compact('child', 'class', 'childProgress'));
    }
    
    /**
     * Get child's progress for materials in a curriculum
     * This is a placeholder - you might want to implement a proper progress tracking system
     */
    private function getChildProgress($childId, $curriculumId)
    {
        // For now, return sample data
        // In a real implementation, you would track which materials have been:
        // - studied (completed)
        // - currently being studied (in progress)
        // - not yet studied (not started)
        
        return [
            'completed' => [], // material IDs that are completed
            'in_progress' => [], // material IDs that are in progress
            'not_started' => [] // material IDs that haven't been started
        ];
    }
}