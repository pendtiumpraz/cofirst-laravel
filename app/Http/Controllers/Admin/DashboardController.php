<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\Syllabus;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Get statistics for dashboard
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_students' => User::role('student')->count(),
            'active_students' => User::role('student')->where('is_active', true)->count(),
            'total_teachers' => User::role('teacher')->count(),
            'active_teachers' => User::role('teacher')->where('is_active', true)->count(),
            'total_parents' => User::role('parent')->count(),
            'active_parents' => User::role('parent')->where('is_active', true)->count(),
            'total_courses' => Course::count(),
            'active_courses' => Course::where('is_active', true)->count(),
            'total_curriculums' => Curriculum::count(),
            'active_curriculums' => Curriculum::where('status', 'active')->count(),
            'total_classes' => ClassName::count(),
            'active_classes' => ClassName::where('status', 'active')->count(),
            'total_enrollments' => Enrollment::count(),
            'active_enrollments' => Enrollment::where('status', 'active')->count(),
            'total_materials' => Material::count(),
            'active_materials' => Material::where('status', 'active')->count(),
            'total_syllabuses' => Syllabus::count(),
            'active_syllabuses' => Syllabus::where('status', 'active')->count(),
        ];

        // Get recent activities
        $recent_enrollments = Enrollment::with(['student', 'className'])
            ->latest()
            ->take(5)
            ->get();

        $recent_users = User::with('roles')
            ->latest()
            ->take(5)
            ->get();

        // Get schedules for calendar
        $schedules = Schedule::where('is_active', true)
            ->with(['className.course', 'className.teacher', 'enrollment.student'])
            ->get();

        // Get today's schedules
        $todaySchedules = Schedule::where('is_active', true)
            ->whereRaw('day_of_week = ?', [now()->dayOfWeek === 0 ? 7 : now()->dayOfWeek])
            ->with(['className.course', 'className.teacher', 'enrollment.student'])
            ->orderBy('start_time')
            ->get();

        // Calculate monthly revenue (current month)
        $monthlyRevenue = Enrollment::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereHas('className.course')
            ->with('className.course')
            ->get()
            ->sum(function($enrollment) {
                return $enrollment->className->course->price ?? 0;
            });
        
        // Format monthly revenue
        $monthlyRevenueFormatted = 'Rp ' . number_format($monthlyRevenue, 0, ',', '.');
        
        // Get recent courses
        $recentCourses = Course::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_enrollments', 'recent_users', 'schedules', 'todaySchedules', 'monthlyRevenueFormatted', 'recentCourses'));
    }
}