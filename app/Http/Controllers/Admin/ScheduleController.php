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
    public function index(Request $request)
    {
        $query = Schedule::with(['className.course', 'enrollment.student', 'teacherAssignment.teacher']);
        
        // Filter by class status if requested
        $classStatus = $request->get('class_status', 'all');
        if ($classStatus !== 'all') {
            if ($classStatus === 'ongoing') {
                $query->forCalendar(); // Only ongoing classes
            } else {
                $query->byClassStatus($classStatus);
            }
        }
        
        $schedules = $query->orderBy('day_of_week')
                          ->orderBy('start_time')
                          ->paginate(20);

        return view('admin.schedules.index', compact('schedules', 'classStatus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classes = ClassName::activeAndOngoing()->with('course')->get();
        
        // Get all active teachers and students
        $teachers = User::role('teacher')
            ->where('is_active', true)
            ->with('teacherAssignments.class')
            ->get();
            
        $students = User::role('student')
            ->where('is_active', true)
            ->with('enrollments.class')
            ->get();
            
        // Keep original data structure for backward compatibility
        $enrollments = Enrollment::whereHas('class', function($q) {
            $q->activeAndOngoing();
        })->with(['student', 'class'])->get();
        $teacherAssignments = TeacherAssignment::whereHas('class', function($q) {
            $q->activeAndOngoing();
        })->with(['teacher', 'class'])->get();

        return view('admin.schedules.create', compact('classes', 'teachers', 'students', 'enrollments', 'teacherAssignments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:class_names,id',
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'required|string|max:100',
            'enrollment_id' => 'required',
            'teacher_assignment_id' => 'required',
            'is_active' => 'boolean',
        ]);

        $class = ClassName::findOrFail($request->class_id);

        // Check if class is still ongoing
        if (!$class->canShowInCalendar()) {
            return redirect()->back()->with('error', 'Cannot create schedule for completed or inactive classes.');
        }

        // Validate quota based on class type
        $maxCapacity = ($class->type === 'group') ? 5 : 1;

        $currentStudentsInSchedule = Schedule::where('class_id', $request->class_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('start_time', $request->start_time)
            ->count();

        if ($currentStudentsInSchedule >= $maxCapacity) {
            return redirect()->back()->with('error', 'Schedule is at full capacity for this class type.');
        }

        // Handle teacher assignment
        $teacherAssignmentId = $request->teacher_assignment_id;
        if (str_starts_with($teacherAssignmentId, 'teacher_')) {
            $teacherId = str_replace('teacher_', '', $teacherAssignmentId);
            $teacherAssignment = TeacherAssignment::firstOrCreate([
                'teacher_id' => $teacherId,
                'class_id' => $request->class_id,
            ]);
            $teacherAssignmentId = $teacherAssignment->id;
        }

        // Handle student enrollment
        $enrollmentId = $request->enrollment_id;
        if (str_starts_with($enrollmentId, 'student_')) {
            $studentId = str_replace('student_', '', $enrollmentId);
            $enrollment = Enrollment::firstOrCreate([
                'student_id' => $studentId,
                'class_id' => $request->class_id,
            ]);
            $enrollmentId = $enrollment->id;
        }

        Schedule::create([
            'class_id' => $request->class_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'room' => $request->room,
            'enrollment_id' => $enrollmentId,
            'teacher_assignment_id' => $teacherAssignmentId,
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
        $classes = ClassName::activeAndOngoing()->with('course')->get();
        
        // Get all active teachers and students
        $teachers = User::role('teacher')
            ->where('is_active', true)
            ->with('teacherAssignments.class')
            ->get();
            
        $students = User::role('student')
            ->where('is_active', true)
            ->with('enrollments.class')
            ->get();
            
        // Keep original data structure for backward compatibility
        $enrollments = Enrollment::whereHas('class', function($q) {
            $q->activeAndOngoing();
        })->with(['student', 'class'])->get();
        $teacherAssignments = TeacherAssignment::whereHas('class', function($q) {
            $q->activeAndOngoing();
        })->with(['teacher', 'class'])->get();

        return view('admin.schedules.edit', compact('schedule', 'classes', 'teachers', 'students', 'enrollments', 'teacherAssignments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'class_id' => 'required|exists:class_names,id',
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'required|string|max:100',
            'enrollment_id' => 'required',
            'teacher_assignment_id' => 'required',
            'is_active' => 'boolean',
        ]);

        $class = ClassName::findOrFail($request->class_id);

        // Check if class is still ongoing
        if (!$class->canShowInCalendar()) {
            return redirect()->back()->with('error', 'Cannot update schedule for completed or inactive classes.');
        }

        // Validate quota based on class type, excluding the current schedule
        $maxCapacity = ($class->type === 'group') ? 5 : 1;

        $currentStudentsInSchedule = Schedule::where('class_id', $request->class_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('start_time', $request->start_time)
            ->where('id', '!=', $schedule->id) // Exclude current schedule
            ->count();

        if ($currentStudentsInSchedule >= $maxCapacity) {
            return redirect()->back()->with('error', 'Schedule is at full capacity for this class type.');
        }

        // Handle teacher assignment
        $teacherAssignmentId = $request->teacher_assignment_id;
        if (str_starts_with($teacherAssignmentId, 'teacher_')) {
            $teacherId = str_replace('teacher_', '', $teacherAssignmentId);
            $teacherAssignment = TeacherAssignment::firstOrCreate([
                'teacher_id' => $teacherId,
                'class_id' => $request->class_id,
            ]);
            $teacherAssignmentId = $teacherAssignment->id;
        }

        // Handle student enrollment
        $enrollmentId = $request->enrollment_id;
        if (str_starts_with($enrollmentId, 'student_')) {
            $studentId = str_replace('student_', '', $enrollmentId);
            $enrollment = Enrollment::firstOrCreate([
                'student_id' => $studentId,
                'class_id' => $request->class_id,
            ]);
            $enrollmentId = $enrollment->id;
        }

        $schedule->update([
            'class_id' => $request->class_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'room' => $request->room,
            'enrollment_id' => $enrollmentId,
            'teacher_assignment_id' => $teacherAssignmentId,
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