<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\ClassName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurriculumController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:teacher']);
    }

    /**
     * Display a listing of curriculums for teacher's classes
     */
    public function index()
    {
        $teacher = Auth::user();
        
        // Get curriculums from teacher's assigned classes
        $curriculums = Curriculum::whereHas('course.classes.teachers', function($query) use ($teacher) {
            $query->where('users.id', $teacher->id);
        })
        ->with([
            'course',
            'syllabuses' => function($query) {
                $query->where('status', 'active')->orderBy('meeting_number');
            }
        ])
        ->where('status', 'active')
        ->withCount('syllabuses')
        ->get();
        
        return view('teacher.curriculums.index', compact('curriculums'));
    }

    /**
     * Display the specified curriculum
     */
    public function show(Curriculum $curriculum)
    {
        $teacher = Auth::user();
        
        // Check if teacher has access to this curriculum (via assigned classes)
        $hasAccess = $curriculum->course->classes()
                                ->whereHas('teachers', function($query) use ($teacher) {
                                    $query->where('users.id', $teacher->id);
                                })
                                ->where('status', 'active')
                                ->exists();
        
        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke kurikulum ini.');
        }
        
        $curriculum->load([
            'course',
            'syllabuses' => function($query) {
                $query->where('status', 'active')->orderBy('meeting_number');
            },
            'syllabuses.materials' => function($query) {
                $query->where('status', 'active')->orderBy('order');
            }
        ]);
        
        // Get teacher's classes using this curriculum
        $teacherClasses = ClassName::where('curriculum_id', $curriculum->id)
                                  ->whereHas('teachers', function($query) use ($teacher) {
                                      $query->where('users.id', $teacher->id);
                                  })
                                  ->where('status', 'active')
                                  ->with(['enrollments.student'])
                                  ->get();
        
        return view('teacher.curriculums.show', compact('curriculum', 'teacherClasses'));
    }

    /**
     * Display syllabuses for a specific curriculum
     */
    public function syllabuses(Curriculum $curriculum)
    {
        $teacher = Auth::user();
        
        // Check if teacher has access to this curriculum (via assigned classes)
        $hasAccess = $curriculum->course->classes()
                                ->whereHas('teachers', function($query) use ($teacher) {
                                    $query->where('users.id', $teacher->id);
                                })
                                ->where('status', 'active')
                                ->exists();
        
        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke kurikulum ini.');
        }
        
        $syllabuses = $curriculum->syllabuses()
                                ->where('status', 'active')
                                ->withCount('materials')
                                ->orderBy('meeting_number')
                                ->get();
        
        return view('teacher.curriculums.syllabuses', compact('curriculum', 'syllabuses'));
    }

    /**
     * Display materials for a specific curriculum
     */
    public function materials(Curriculum $curriculum)
    {
        $teacher = Auth::user();
        
        // Check if teacher has access to this curriculum (via assigned classes)
        $hasAccess = $curriculum->course->classes()
                                ->whereHas('teachers', function($query) use ($teacher) {
                                    $query->where('users.id', $teacher->id);
                                })
                                ->where('status', 'active')
                                ->exists();
        
        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke kurikulum ini.');
        }
        
        $materials = $curriculum->activeMaterials()
                               ->with('syllabus')
                               ->get();
        
        return view('teacher.curriculums.materials', compact('curriculum', 'materials'));
    }
}