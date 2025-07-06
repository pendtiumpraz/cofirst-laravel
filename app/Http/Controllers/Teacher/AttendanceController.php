<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display the attendance index page.
     */
    public function index()
    {
        return view('teacher.attendance.index')->with([
            'message' => 'Attendance management feature is coming soon!',
            'attendances' => collect()
        ]);
    }

    /**
     * Display today's attendance page.
     */
    public function today()
    {
        return view('teacher.attendance.today')->with([
            'message' => 'Today\'s attendance feature is coming soon!',
            'schedules' => collect()
        ]);
    }
}
