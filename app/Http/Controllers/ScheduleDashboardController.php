<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ScheduleDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        $schedules = Schedule::where('is_active', true)
            ->with(['className.course', 'className.teachers', 'enrollment.student', 'teacherAssignment.teacher'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $days[$date->dayOfWeekIso] = [
                'date' => $date->format('Y-m-d'),
                'name' => $date->format('l'),
                'schedules' => $schedules[$date->dayOfWeekIso] ?? collect(),
            ];
        }

        // Prepare time slots
        $timeSlots = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $time = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
            $timeSlots[] = $time;
        }

        return view('dashboard', compact('days', 'timeSlots'));
    }
}