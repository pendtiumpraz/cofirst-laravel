<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassName;
use App\Models\TeacherAssignment;
use App\Models\Enrollment;

class ScheduleDataController extends Controller
{
    public function getTeachersByClass($class_id)
    {
        $class = ClassName::with('teachers')->findOrFail($class_id);
        $teachers = $class->teachers->map(function ($teacher) use ($class_id) {
            // Try to find existing TeacherAssignment
            $teacherAssignment = TeacherAssignment::where('teacher_id', $teacher->id)
                                                 ->where('class_id', $class_id)
                                                 ->first();
            
            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'assignment_id' => $teacherAssignment ? $teacherAssignment->id : null,
                'has_assignment' => $teacherAssignment ? true : false
            ];
        });

        return response()->json($teachers);
    }

    public function getStudentsByClass($class_id)
    {
        $class = ClassName::findOrFail($class_id);
        $enrollments = Enrollment::where('class_id', $class_id)
                                ->where('status', 'active')
                                ->with('student')
                                ->get();

        $students = $enrollments->map(function ($enrollment) {
            return [
                'id' => $enrollment->student->id,
                'name' => $enrollment->student->name,
                'email' => $enrollment->student->email,
                'enrollment_id' => $enrollment->id
            ];
        });

        return response()->json($students);
    }

    public function getEnrollmentsByClass($class_id)
    {
        $class = ClassName::findOrFail($class_id);
        $enrollments = Enrollment::where('class_id', $class_id)
                                ->with('student')
                                ->get();

        return response()->json($enrollments);
    }
}