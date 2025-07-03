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

        $schedules = Schedule::forCalendar()
            ->with(['className.course', 'className.teacher', 'enrollment.student', 'teacherAssignment.teacher'])
            ->when($user->hasRole('teacher'), function ($query) use ($user) {
                // If teacher, only show schedules assigned to them
                $query->whereHas('teacherAssignment', function ($q) use ($user) {
                    $q->where('teacher_id', $user->id);
                });
            })
            ->when($user->hasRole('student'), function ($query) use ($user) {
                // If student, only show schedules from their enrolled classes
                $query->whereHas('enrollment', function ($q) use ($user) {
                    $q->where('student_id', $user->id)
                      ->where('status', 'active');
                });
            })
            ->get()
            ->filter(function ($schedule) {
                // Additional filter to ensure all participants are active
                $teacherIsActive = $schedule->teacherAssignment?->teacher?->is_active ?? true;
                $studentIsActive = $schedule->enrollment?->student?->is_active ?? true;
                return $teacherIsActive && $studentIsActive;
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