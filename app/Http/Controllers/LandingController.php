<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Display the landing page.
     */
    public function index()
    {
        $featuredCourses = Course::active()
            ->take(6)
            ->get();
            
        // Get featured teachers with their profile photos
        $featuredTeachers = User::role('teacher')
            ->where('is_active', true)
            ->with('profile')
            ->take(8)
            ->get();
            
        // Get top students (based on points if gamification is active)
        $topStudents = User::role('student')
            ->where('is_active', true)
            ->with(['profile', 'points'])
            ->orderByDesc(function($query) {
                $query->select('total_earned')
                    ->from('user_points')
                    ->whereColumn('user_id', 'users.id')
                    ->limit(1);
            })
            ->take(6)
            ->get();
            
        // Get parent testimonials
        $testimonials = Testimonial::where('is_active', true)
            ->where('is_featured', true)
            ->with('user')
            ->take(6)
            ->get();

        return view('landing', compact('featuredCourses', 'featuredTeachers', 'topStudents', 'testimonials'));
    }

    /**
     * Display all courses.
     */
    public function courses()
    {
        $courses = Course::active()
            ->paginate(12);

        return view('courses', compact('courses'));
    }

    /**
     * Display course details.
     */
    public function courseDetail(Course $course)
    {
        return view('course-detail', compact('course'));
    }

    /**
     * Display about page.
     */
    public function about()
    {
        return view('about');
    }

    /**
     * Display contact page.
     */
    public function contact()
    {
        return view('contact');
    }
}
