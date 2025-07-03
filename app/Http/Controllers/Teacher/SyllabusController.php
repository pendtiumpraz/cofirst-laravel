<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Syllabus;
use App\Models\Curriculum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyllabusController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:teacher']);
    }

    /**
     * Display a listing of syllabuses for teacher's classes
     */
    public function index()
    {
        $teacher = Auth::user();
        
        // Get syllabuses from teacher's classes
        $syllabuses = Syllabus::whereHas('curriculum.course.classes', function($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id)
                  ->where('status', 'active');
        })
        ->with([
            'curriculum.course',
            'materials' => function($query) {
                $query->where('status', 'active')->orderBy('order');
            }
        ])
        ->where('status', 'active')
        ->withCount('materials')
        ->orderBy('meeting_number')
        ->get();
        
        return view('teacher.syllabuses.index', compact('syllabuses'));
    }

    /**
     * Display the specified syllabus
     */
    public function show(Syllabus $syllabus)
    {
        $teacher = Auth::user();
        
        // Check if teacher has access to this syllabus
        $hasAccess = $syllabus->curriculum->course->classes()
                                ->where('teacher_id', $teacher->id)
                                ->where('status', 'active')
                                ->exists();
        
        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke silabus ini.');
        }
        
        $syllabus->load([
            'curriculum.course',
            'materials' => function($query) {
                $query->where('status', 'active')->orderBy('order');
            }
        ]);
        
        return view('teacher.syllabuses.show', compact('syllabus'));
    }
}