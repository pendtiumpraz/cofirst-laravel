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
        $class = ClassName::findOrFail($class_id);
        $teacherAssignments = TeacherAssignment::where('class_id', $class_id)
                                ->with('teacher')
                                ->get();

        return response()->json($teacherAssignments);
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