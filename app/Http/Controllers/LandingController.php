<?php

namespace App\Http\Controllers;

use App\Models\Course;
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

        return view('landing', compact('featuredCourses'));
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
