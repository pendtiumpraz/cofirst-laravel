<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:parent']);
    }

    /**
     * Display parent dashboard with purchased and available classes
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get parent's children with their enrollments and class details
        $children = $user->children()->with([
            'enrollments' => function($query) {
                $query->where('status', 'active');
            },
            'enrollments.class.course',
            'enrollments.class.teacher'
        ])->get();

        // Get purchased classes (enrolled classes for all children)
        $purchasedClasses = collect();
        foreach ($children as $child) {
            foreach ($child->enrollments as $enrollment) {
                $purchasedClasses->push([
                    'child' => $child,
                    'class' => $enrollment->class,
                    'enrollment' => $enrollment
                ]);
            }
        }

        // Get available classes (active classes that children are not enrolled in)
        $enrolledClassIds = $purchasedClasses->pluck('class.id')->toArray();
        
        $availableClasses = ClassName::with(['course', 'teacher'])
            ->where('status', 'active')
            ->where('is_active', true)
            ->whereNotIn('id', $enrolledClassIds)
            ->where('max_students', '>', function($query) {
                $query->selectRaw('count(*)')
                    ->from('enrollments')
                    ->whereColumn('enrollments.class_id', 'class_names.id')
                    ->where('enrollments.status', 'active');
            })
            ->get();

        // Get children's schedules for calendar
        $childrenIds = $children->pluck('id')->toArray();
        $schedules = Schedule::forCalendar()
            ->with(['className.course', 'teacherAssignment.teacher', 'enrollment.student'])
            ->whereHas('enrollment', function ($query) use ($childrenIds) {
                $query->whereIn('student_id', $childrenIds)
                      ->where('status', 'active');
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Get today's schedule
        $today = Carbon::today();
        $todayDayOfWeek = $today->dayOfWeek === 0 ? 7 : $today->dayOfWeek; // Convert Sunday from 0 to 7
        $todaySchedules = $schedules->filter(function ($schedule) use ($todayDayOfWeek) {
            return $schedule->day_of_week == $todayDayOfWeek;
        })->sortBy('start_time');

        // Calculate summary statistics
        $stats = [
            'total_children' => $children->count(),
            'active_classes' => $purchasedClasses->count(),
            'available_classes' => $availableClasses->count(),
            'total_spent' => $purchasedClasses->sum(function($item) {
                return $item['class']->course->price ?? 0;
            })
        ];

        return view('parent.dashboard', compact('children', 'purchasedClasses', 'availableClasses', 'schedules', 'todaySchedules', 'stats'));
    }
}