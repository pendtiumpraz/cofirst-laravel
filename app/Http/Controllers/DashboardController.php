<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ClassName;
use App\Models\FinancialTransaction;
use App\Models\Schedule; // Import Schedule model
use Carbon\Carbon; // Import Carbon for date manipulation

class DashboardController extends Controller
{
    /**
     * Display the dashboard for all users.
     */
    public function index()
    {
        $user = Auth::user();
        $schedules = collect(); // Initialize an empty collection for schedules

        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            // For admin/superadmin, fetch all active schedules from ongoing classes
            $schedules = Schedule::forCalendar()
                                ->with(['className.course', 'teacherAssignment.teacher', 'enrollment.student'])
                                ->orderBy('day_of_week')
                                ->orderBy('start_time')
                                ->get();
        } elseif ($user->hasRole('teacher')) {
            // For teachers, fetch schedules assigned to them from ongoing classes only
            $schedules = Schedule::forCalendar()
                                ->with(['className.course', 'teacherAssignment.teacher', 'enrollment.student'])
                                ->whereHas('teacherAssignment', function ($query) use ($user) {
                                    $query->where('teacher_id', $user->id);
                                })
                                ->orderBy('day_of_week')
                                ->orderBy('start_time')
                                ->get();
        } elseif ($user->hasRole('student')) {
            // For students, fetch schedules from their enrolled classes that are ongoing
            $schedules = Schedule::forCalendar()
                                ->with(['className.course', 'teacherAssignment.teacher', 'enrollment.student'])
                                ->whereHas('enrollment', function ($query) use ($user) {
                                    $query->where('student_id', $user->id)
                                          ->where('status', 'active');
                                })
                                ->orderBy('day_of_week')
                                ->orderBy('start_time')
                                ->get();
        }

        // Filter today's schedules based on current day of week
        $today = Carbon::today();
        $todayDayOfWeek = $today->dayOfWeek === 0 ? 7 : $today->dayOfWeek; // Convert Sunday from 0 to 7
        $todaySchedules = $schedules->filter(function ($schedule) use ($todayDayOfWeek) {
            return $schedule->day_of_week == $todayDayOfWeek;
        })->sortBy('start_time');

        // Return the main dashboard view for all users, passing schedules
        return view('dashboard', compact('user', 'schedules', 'todaySchedules'));
    }
}