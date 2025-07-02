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
            // For admin/superadmin, fetch all schedules for the next 7 days
            $schedules = Schedule::with(['className', 'teacherAssignment.teacher', 'enrollment.student'])
                                ->where('schedule_date', '>=', Carbon::today())
                                ->where('schedule_date', '<=', Carbon::today()->addDays(6))
                                ->orderBy('schedule_date')
                                ->orderBy('schedule_time')
                                ->get();
        } elseif ($user->hasRole('teacher')) {
            // For teachers, fetch schedules assigned to them for the next 7 days
            $schedules = Schedule::with(['className', 'teacherAssignment.teacher', 'enrollment.student'])
                                ->whereHas('teacherAssignment', function ($query) use ($user) {
                                    $query->where('teacher_id', $user->id);
                                })
                                ->where('schedule_date', '>=', Carbon::today())
                                ->where('schedule_date', '<=', Carbon::today()->addDays(6))
                                ->orderBy('schedule_date')
                                ->orderBy('schedule_time')
                                ->get();
        }

        // Return the main dashboard view for all users, passing schedules
        return view('dashboard', compact('user', 'schedules'));
    }
}