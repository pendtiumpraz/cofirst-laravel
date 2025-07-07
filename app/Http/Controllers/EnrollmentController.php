<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\ClassName;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments.
     */
    public function index()
    {
        $enrollments = Enrollment::with(['student', 'class.course'])->paginate(20);
        $courses = \App\Models\Course::where('is_active', true)->get();
        return view('admin.enrollments.index', compact('enrollments', 'courses'));
    }

    /**
     * Show the form for creating a new enrollment.
     */
    public function create()
    {
        $students = User::role('student')->where('is_active', true)->get();
        $classes = ClassName::where('is_active', true)
                          ->whereIn('status', ['planned', 'active'])
                          ->with(['course', 'teacher'])
                          ->get();
        return view('admin.enrollments.create', compact('students', 'classes'));
    }

    /**
     * Store a newly created enrollment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:class_names,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,completed,dropped,suspended'
        ]);

        // Check if student is already enrolled in this class
        $existingEnrollment = Enrollment::where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->first();

        if ($existingEnrollment) {
            return redirect()->back()->with('error', 'Student is already enrolled in this class.');
        }

        // Check class capacity
        $class = ClassName::findOrFail($request->class_id);
        if ($class->enrollments()->count() >= $class->capacity) {
            return redirect()->back()->with('error', 'Class is at full capacity.');
        }

        Enrollment::create([
            'student_id' => $request->student_id,
            'class_id' => $request->class_id,
            'enrollment_date' => $request->enrollment_date,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.enrollments.index')->with('success', 'Enrollment created successfully.');
    }

    /**
     * Display the specified enrollment.
     */
    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['student', 'class.course', 'class.teacher']);
        return view('admin.enrollments.show', compact('enrollment'));
    }

    /**
     * Show the form for editing the specified enrollment.
     */
    public function edit(Enrollment $enrollment)
    {
        $students = User::role('student')->where('is_active', true)->get();
        $classes = ClassName::where('is_active', true)
                          ->whereIn('status', ['planned', 'active'])
                          ->with(['course', 'teacher'])
                          ->get();
        return view('admin.enrollments.edit', compact('enrollment', 'students', 'classes'));
    }

    /**
     * Update the specified enrollment in storage.
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:class_names,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,completed,dropped,suspended'
        ]);

        // Check if student is already enrolled in this class (excluding current enrollment)
        $existingEnrollment = Enrollment::where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->where('id', '!=', $enrollment->id)
            ->first();

        if ($existingEnrollment) {
            return redirect()->back()->with('error', 'Student is already enrolled in this class.');
        }

        $enrollment->update([
            'student_id' => $request->student_id,
            'class_id' => $request->class_id,
            'enrollment_date' => $request->enrollment_date,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.enrollments.index')->with('success', 'Enrollment updated successfully.');
    }

    /**
     * Remove the specified enrollment from storage.
     */
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();
        return redirect()->route('admin.enrollments.index')->with('success', 'Enrollment deleted successfully.');
    }

    /**
     * Toggle enrollment status.
     */
    public function toggleStatus(Enrollment $enrollment)
    {
        $newStatus = $enrollment->status === 'active' ? 'suspended' : 'active';
        $enrollment->update(['status' => $newStatus]);
        
        return redirect()->route('admin.enrollments.index')->with('success', "Enrollment status updated to {$newStatus}.");
    }
}
