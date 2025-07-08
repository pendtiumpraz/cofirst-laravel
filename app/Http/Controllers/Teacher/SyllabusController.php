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
        
        // Get syllabuses from teacher's assigned classes
        $syllabuses = Syllabus::whereHas('curriculum.course.classes.teachers', function($query) use ($teacher) {
            $query->where('users.id', $teacher->id);
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
        
        // Check if teacher has access to this syllabus (via assigned classes)
        $hasAccess = $syllabus->curriculum->course->classes()
                                ->whereHas('teachers', function($query) use ($teacher) {
                                    $query->where('users.id', $teacher->id);
                                })
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