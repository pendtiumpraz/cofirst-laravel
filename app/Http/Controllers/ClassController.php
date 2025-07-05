<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassName;
use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ClassController extends Controller
{
    /**
     * Display a listing of classes.
     */
    public function index()
    {
        $classes = ClassName::with(['course', 'teacher'])
            ->withCount('enrollments')
            ->paginate(20);
            
        // Debug logging
        \Log::info('Admin Classes Debug', [
            'classes_count' => $classes->count(),
            'total_classes' => $classes->total(),
            'current_page' => $classes->currentPage(),
            'first_class' => $classes->first() ? [
                'id' => $classes->first()->id,
                'name' => $classes->first()->name,
                'course' => $classes->first()->course?->name,
                'teacher' => $classes->first()->teacher?->name,
                'status' => $classes->first()->status,
                'is_active' => $classes->first()->is_active,
            ] : null
        ]);
            
        return view('admin.classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new class.
     */
    public function create()
    {
        $courses = Course::where('is_active', true)->get();
        $teachers = User::role('teacher')->where('is_active', true)->get();
        return view('admin.classes.create', compact('courses', 'teachers'));
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:private_home_call,private_office_1on1,private_online_1on1,public_school_extracurricular,offline_seminar,online_webinar,group_class_3_5_kids',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
        ]);

        $classData = [
            'name' => $request->name,
            'type' => $request->type,
            'course_id' => $request->course_id,
            'teacher_id' => $request->teacher_id,
            'max_students' => $request->capacity,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->boolean('is_active', true) ? 'active' : 'planned',
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = 'class-photos/' . uniqid() . '-' . time() . '.' . $photo->getClientOriginalExtension();
            
            // Process image with Intervention
            $image = Image::make($photo);
            $image->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            // Save to storage
            Storage::disk('public')->put($filename, $image->encode());
            $classData['photo_path'] = $filename;
        }

        $class = ClassName::create($classData);

        // Record initial teacher assignment
        \App\Models\TeacherAssignment::create([
            'teacher_id' => $request->teacher_id,
            'class_id' => $class->id,
            'assigned_at' => now(),
        ]);

        return redirect()->route('admin.classes.index')->with('success', 'Class created successfully.');
    }

    /**
     * Display the specified class.
     */
    public function show(ClassName $class)
    {
        $class->load(['course', 'teacher', 'enrollments.student']);
        return view('admin.classes.show', compact('class'));
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit(ClassName $class)
    {
        $courses = Course::where('is_active', true)->get();
        $teachers = User::role('teacher')->where('is_active', true)->get();
        return view('admin.classes.edit', compact('class', 'courses', 'teachers'));
    }

    /**
     * Update the specified class in storage.
     */
    public function update(Request $request, ClassName $class)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:private_home_call,private_office_1on1,private_online_1on1,public_school_extracurricular,offline_seminar,online_webinar,group_class_3_5_kids',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
        ]);

        // Check if teacher has changed
        if ($class->teacher_id !== (int)$request->teacher_id) {
            // Unassign current teacher
            \App\Models\TeacherAssignment::where('class_id', $class->id)
                ->whereNull('unassigned_at')
                ->update(['unassigned_at' => now()]);

            // Assign new teacher
            \App\Models\TeacherAssignment::create([
                'teacher_id' => $request->teacher_id,
                'class_id' => $class->id,
                'assigned_at' => now(),
            ]);
        }

        $classData = [
            'name' => $request->name,
            'type' => $request->type,
            'course_id' => $request->course_id,
            'teacher_id' => $request->teacher_id,
            'max_students' => $request->capacity,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->boolean('is_active', true) ? 'active' : 'planned',
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($class->photo_path) {
                Storage::disk('public')->delete($class->photo_path);
            }

            $photo = $request->file('photo');
            $filename = 'class-photos/' . $class->id . '-' . time() . '.' . $photo->getClientOriginalExtension();
            
            // Process image with Intervention
            $image = Image::make($photo);
            $image->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            
            // Save to storage
            Storage::disk('public')->put($filename, $image->encode());
            $classData['photo_path'] = $filename;
        }

        $class->update($classData);

        return redirect()->route('admin.classes.index')->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified class from storage.
     */
    public function destroy(ClassName $class)
    {
        // Check if class has enrollments
        if ($class->enrollments()->count() > 0) {
            return redirect()->route('admin.classes.index')->with('error', 'Cannot delete class with existing enrollments.');
        }

        $class->delete();
        return redirect()->route('admin.classes.index')->with('success', 'Class deleted successfully.');
    }

    /**
     * Show students in the class.
     */
    public function students(ClassName $class)
    {
        $class->load(['enrollments.student', 'course', 'teacher']);
        return view('admin.classes.students', compact('class'));
    }

    /**
     * Add student to class.
     */
    public function addStudent(Request $request, ClassName $class)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id'
        ]);

        // Check if student is already enrolled
        if ($class->enrollments()->where('student_id', $request->student_id)->exists()) {
            return redirect()->back()->with('error', 'Student is already enrolled in this class.');
        }

        // Check class capacity
        if ($class->enrollments()->count() >= $class->capacity) {
            return redirect()->back()->with('error', 'Class is at full capacity.');
        }

        Enrollment::create([
            'student_id' => $request->student_id,
            'class_id' => $class->id,
            'enrollment_date' => now(),
            'status' => 'active'
        ]);

        return redirect()->back()->with('success', 'Student added to class successfully.');
    }

    /**
     * Remove student from class.
     */
    public function removeStudent(ClassName $class, User $student)
    {
        $enrollment = $class->enrollments()->where('student_id', $student->id)->first();
        
        if ($enrollment) {
            $enrollment->delete();
            return redirect()->back()->with('success', 'Student removed from class successfully.');
        }

        return redirect()->back()->with('error', 'Student not found in this class.');
    }
}
