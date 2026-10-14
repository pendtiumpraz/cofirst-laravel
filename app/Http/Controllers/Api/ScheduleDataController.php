<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassName;
use App\Models\Enrollment;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;

/**
 * JSON helpers for the admin schedule form (routes admin.schedule-data.*,
 * role:admin|superadmin + SchedulePolicy).
 *
 * Data minimisation (UU PDP Pasal 16): only ids and names are returned — never
 * emails or full user records — and only to staff who manage schedules.
 */
class ScheduleDataController extends Controller
{
    /**
     * Teachers of a class with their current assignment id.
     */
    public function teachers(ClassName $class): JsonResponse
    {
        $this->authorize('viewAny', Schedule::class);

        $assignments = $class->activeTeacherAssignments()->get(['id', 'teacher_id'])->keyBy('teacher_id');

        $teachers = $class->teachers()->orderBy('name')->get(['users.id', 'users.name'])
            ->map(fn ($teacher) => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'assignment_id' => $assignments->get($teacher->id)?->id,
            ])->values();

        return response()->json(['data' => $teachers]);
    }

    /**
     * Students holding a seat (pending/active) in a class, with their enrollment id.
     */
    public function students(ClassName $class): JsonResponse
    {
        $this->authorize('viewAny', Schedule::class);

        $students = $class->enrollments()
            ->whereIn('status', Enrollment::SEAT_STATUSES)
            ->with('student:id,name')
            ->get(['id', 'student_id', 'status'])
            ->filter(fn (Enrollment $e) => $e->student !== null)
            ->sortBy(fn (Enrollment $e) => mb_strtolower($e->student->name))
            ->map(fn (Enrollment $e) => [
                'enrollment_id' => $e->id,
                'id' => $e->student->id,
                'name' => $e->student->name,
                'pending' => $e->status === Enrollment::STATUS_PENDING,
            ])->values();

        return response()->json(['data' => $students]);
    }
}
