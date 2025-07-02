<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    /**
     * Display a listing of courses.
     */
    public function index()
    {
        $courses = Course::paginate(20);
        return view('admin.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create()
    {
        return view('admin.courses.create');
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_hours' => 'required|integer|min:1',
            'level' => 'required|in:beginner,intermediate,advanced',
            'is_active' => 'boolean'
        ]);

        Course::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'duration_hours' => $request->duration_hours,
            'level' => $request->level,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.courses.index')->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course)
    {
        return view('admin.courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(Course $course)
    {
        return view('admin.courses.edit', compact('course'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_hours' => 'required|integer|min:1',
            'level' => 'required|in:beginner,intermediate,advanced',
            'is_active' => 'boolean'
        ]);

        $course->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'duration_hours' => $request->duration_hours,
            'level' => $request->level,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
    }

    /**
     * Toggle course status.
     */
    public function toggleStatus(Course $course)
    {
        $course->update(['is_active' => !$course->is_active]);
        
        $status = $course->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.courses.index')->with('success', "Course {$status} successfully.");
    }
}
