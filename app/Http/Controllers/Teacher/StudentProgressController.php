<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentProgressController extends Controller
{
    /**
     * Display student progress overview.
     */
    public function index()
    {
        return view('teacher.student-progress.index')->with([
            'message' => 'Student Progress tracking feature is coming soon!',
            'students' => collect()
        ]);
    }

    /**
     * Show detailed progress for a specific student.
     */
    public function show(string $id)
    {
        return view('teacher.student-progress.show')->with([
            'message' => 'Detailed student progress feature is coming soon!',
            'student' => null,
            'progress' => collect()
        ]);
    }
}
