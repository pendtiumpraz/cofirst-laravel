<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\ClassName;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\TeacherAssignment;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = Schedule::with(['className', 'enrollment.student', 'teacherAssignment.teacher'])
                            ->orderBy('schedule_date', 'desc')
                            ->orderBy('schedule_time', 'asc')
                            ->paginate(20);

        return view('admin.schedules.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classes = ClassName::where('status', 'active')->with('course')->get();
        $teachers = User::role('teacher')->where('is_active', true)->get();
        $students = User::role('student')->where('is_active', true)->get();
        $enrollments = Enrollment::with(['student', 'class'])->get(); // Fetch all enrollments for selection

        return view('admin.schedules.create', compact('classes', 'teachers', 'students', 'enrollments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:class_names,id',
            'schedule_date' => 'required|date',
            'schedule_time' => 'required|date_format:H:i',
            'enrollment_id' => 'required|exists:enrollments,id',
            'teacher_assignment_id' => 'required|exists:teacher_assignments,id',
            'is_active' => 'boolean',
        ]);

        $class = ClassName::findOrFail($request->class_id);

        // Validate quota based on class type
        $maxCapacity = ($class->type === 'group') ? 5 : 1;

        $currentStudentsInSchedule = Schedule::where('class_id', $request->class_id)
            ->where('schedule_date', $request->schedule_date)
            ->where('schedule_time', $request->schedule_time)
            ->count();

        if ($currentStudentsInSchedule >= $maxCapacity) {
            return redirect()->back()->with('error', 'Schedule is at full capacity for this class type.');
        }

        Schedule::create([
            'class_id' => $request->class_id,
            'schedule_date' => $request->schedule_date,
            'schedule_time' => $request->schedule_time,
            'enrollment_id' => $request->enrollment_id,
            'teacher_assignment_id' => $request->teacher_assignment_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.schedules.index')->with('success', 'Schedule created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        $schedule->load(['className', 'enrollment.student', 'teacherAssignment.teacher']);
        return view('admin.schedules.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Schedule $schedule)
    {
        $classes = ClassName::where('status', 'active')->with('course')->get();
        $teachers = User::role('teacher')->where('is_active', true)->get();
        $students = User::role('student')->where('is_active', true)->get();
        $enrollments = Enrollment::with(['student', 'class'])->get();

        return view('admin.schedules.edit', compact('schedule', 'classes', 'teachers', 'students', 'enrollments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'class_id' => 'required|exists:class_names,id',
            'schedule_date' => 'required|date',
            'schedule_time' => 'required|date_format:H:i',
            'enrollment_id' => 'required|exists:enrollments,id',
            'teacher_assignment_id' => 'required|exists:teacher_assignments,id',
            'is_active' => 'boolean',
        ]);

        $class = ClassName::findOrFail($request->class_id);

        // Validate quota based on class type, excluding the current schedule
        $maxCapacity = ($class->type === 'group') ? 5 : 1;

        $currentStudentsInSchedule = Schedule::where('class_id', $request->class_id)
            ->where('schedule_date', $request->schedule_date)
            ->where('schedule_time', $request->schedule_time)
            ->where('id', '!=', $schedule->id) // Exclude current schedule
            ->count();

        if ($currentStudentsInSchedule >= $maxCapacity) {
            return redirect()->back()->with('error', 'Schedule is at full capacity for this class type.');
        }

        $schedule->update([
            'class_id' => $request->class_id,
            'schedule_date' => $request->schedule_date,
            'schedule_time' => $request->schedule_time,
            'enrollment_id' => $request->enrollment_id,
            'teacher_assignment_id' => $request->teacher_assignment_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.schedules.index')->with('success', 'Schedule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('admin.schedules.index')->with('success', 'Schedule deleted successfully.');
    }
}