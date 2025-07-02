<?php

namespace App\Http\Controllers;

use App\Models\ClassReport;
use App\Models\ClassName;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Curriculum;
use App\Models\Syllabus;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassReportController extends Controller
{
    public function create()
    {
        $teacher = Auth::user();

        // Dapatkan kelas yang diajar oleh guru ini
        $classes = ClassName::where('teacher_id', $teacher->id)->get();

        return view('teacher.class_reports.create', compact('classes'));
    }

    public function getSchedulesAndStudents(Request $request)
    {
        $classId = $request->input('class_id');
        $teacher = Auth::user();

        // Dapatkan jadwal untuk kelas yang dipilih dan guru yang login
        $schedules = Schedule::where('class_id', $classId)
                            ->whereHas('class', function ($query) use ($teacher) {
                                $query->where('teacher_id', $teacher->id);
                            })
                            ->get();

        // Dapatkan siswa yang terdaftar di kelas yang dipilih
        $students = User::whereHas('enrollments', function ($query) use ($classId) {
                                $query->where('class_id', $classId);
                            })
                            ->get();

        // Dapatkan kurikulum, silabus, dan materi terkait dengan kelas yang dipilih
        $class = ClassName::with('curriculum.syllabuses.materials')->find($classId);
        $curriculum = $class->curriculum;
        $syllabuses = $curriculum ? $curriculum->syllabuses : collect();
        $materials = collect();
        foreach ($syllabuses as $syllabus) {
            $materials = $materials->merge($syllabus->materials);
        }

        return response()->json(compact('schedules', 'students', 'curriculum', 'syllabuses', 'materials'));
    }

    public function store(Request $request)
    {
        $teacher = Auth::user();

        $request->validate([
            'class_id' => 'required|exists:class_names,id',
            'student_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'report_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'meeting_number' => 'required|integer|min:1',
            'curriculum_id' => 'required|exists:curriculums,id',
            'syllabus_id' => 'required|exists:syllabuses,id',
            'material_id' => 'required|exists:materials,id',
            'learning_concept' => 'required|string',
            'remember_understanding' => 'nullable|string',
            'understand_comprehension' => 'nullable|string',
            'apply_application' => 'nullable|string',
            'analyze_analysis' => 'nullable|string',
            'evaluate_evaluation' => 'nullable|string',
            'create_creation' => 'nullable|string',
            'notes_recommendations' => 'nullable|string',
            'follow_up_notes' => 'nullable|string',
            'learning_media_link' => 'nullable|url',
        ]);

        // Pastikan guru yang login adalah guru yang mengajar kelas ini
        $class = ClassName::where('id', $request->class_id)->where('teacher_id', $teacher->id)->firstOrFail();

        ClassReport::create(array_merge($request->all(), [
            'teacher_id' => $teacher->id,
        ]));

        return redirect()->route('dashboard')->with('success', 'Berita acara berhasil disimpan!');
    }
}