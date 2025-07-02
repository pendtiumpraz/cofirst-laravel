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

        $schedules = Schedule::with(['class.teacher', 'class.enrollments.student'])
            ->whereHas('class', function ($query) use ($user) {
                // Only show schedules for active classes
                $query->where('status', 'active');

                // If teacher, only show their classes
                if ($user->hasRole('teacher')) {
                    $query->where('teacher_id', $user->id);
                }
            })
            ->where('is_active', true) // Only show active schedules
            ->get()
            ->filter(function ($schedule) {
                // Filter out schedules where teacher or students are inactive
                $teacherIsActive = $schedule->class->teacher->is_active ?? false;
                $studentsAreActive = $schedule->class->enrollments->every(function ($enrollment) {
                    return $enrollment->student->is_active ?? false;
                });
                return $teacherIsActive && $studentsAreActive;
            })
            ->groupBy(function ($schedule) {
                return $schedule->day_of_week; // Group by day of week
            })
            ->map(function ($dailySchedules) {
                return $dailySchedules->sortBy('start_time'); // Sort by time within each day
            });

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