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
use Carbon\Carbon;

class ClassReportController extends Controller
{


    /**
     * Display a listing of class reports.
     */
    public function index()
    {
        $this->authorize('viewAny', ClassReport::class);
        
        $user = Auth::user();
        
        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            // Admin can see all class reports
            $classReports = ClassReport::with([
                'teacher', 'class.course', 'student', 'schedule', 
                'curriculum', 'syllabus', 'material'
            ])->orderBy('report_date', 'desc')->paginate(15);
        } else {
            // Teacher can only see their own class reports
            $classReports = ClassReport::with([
                'teacher', 'class.course', 'student', 'schedule', 
                'curriculum', 'syllabus', 'material'
            ])->where('teacher_id', $user->id)
              ->orderBy('report_date', 'desc')
              ->paginate(15);
        }
        
        return view('class-reports.index', compact('classReports'));
    }

    /**
     * Display the specified class report.
     */
    public function show(ClassReport $classReport)
    {
        $this->authorize('view', $classReport);
        
        $classReport->load([
            'teacher', 'class.course', 'student', 'schedule', 
            'curriculum', 'syllabus', 'material'
        ]);
        
        return view('class-reports.show', compact('classReport'));
    }
    public function create(Request $request)
    {
        $this->authorize('create', ClassReport::class);
        
        $user = Auth::user();
        $scheduleId = $request->get('schedule_id');
        
        // Get teacher's schedules that haven't been reported yet
        $availableSchedules = Schedule::with(['className.course', 'enrollment.student'])
            ->whereHas('teacherAssignment', function ($query) use ($user) {
                $query->where('teacher_id', $user->id);
            })
            ->whereDoesntHave('classReports')
            ->get();
            
        $selectedSchedule = null;
        if ($scheduleId) {
            $selectedSchedule = Schedule::with(['className.course', 'enrollment.student'])
                ->where('id', $scheduleId)
                ->first();
        }
        
        // Get curriculum, syllabus, and materials for dropdowns
        $curriculums = Curriculum::all();
        $syllabuses = Syllabus::all();
        $materials = Material::all();
        
        return view('class-reports.create', compact(
            'availableSchedules', 'selectedSchedule', 'curriculums', 'syllabuses', 'materials'
        ));
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
        $this->authorize('create', ClassReport::class);
        
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

        return redirect()->route('class-reports.index')->with('success', 'Berita acara berhasil disimpan!');
    }

    /**
     * Show the form for editing the specified class report.
     */
    public function edit(ClassReport $classReport)
    {
        $this->authorize('update', $classReport);
        
        $user = Auth::user();
        
        // Check grace period for teachers (policy handles role authorization)
        if ($user->hasRole('teacher')) {
            $weekAgo = Carbon::now()->subWeek();
            if ($classReport->report_date < $weekAgo->toDateString()) {
                return back()->withErrors(['edit' => 'Berita acara hanya dapat diedit dalam 1 minggu setelah tanggal kelas.']);
            }
        }
        
        $classReport->load(['schedule.className.course', 'schedule.enrollment.student']);
        
        $curriculums = Curriculum::all();
        $syllabuses = Syllabus::all();
        $materials = Material::all();
        
        return view('class-reports.edit', compact('classReport', 'curriculums', 'syllabuses', 'materials'));
    }

    /**
     * Update the specified class report in storage.
     */
    public function update(Request $request, ClassReport $classReport)
    {
        $this->authorize('update', $classReport);
        
        $request->validate([
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
        
        $classReport->update($request->all());
        
        return redirect()->route('class-reports.index')
            ->with('success', 'Berita acara berhasil diperbarui.');
    }

    /**
     * Remove the specified class report from storage.
     */
    public function destroy(ClassReport $classReport)
    {
        $this->authorize('delete', $classReport);
        
        $classReport->delete();
        
        return redirect()->route('class-reports.index')
            ->with('success', 'Berita acara berhasil dihapus.');
    }
    
    /**
     * Get pending class reports for notifications
     */
    public function getPendingReports($teacherId)
    {
        $oneWeekAgo = Carbon::now()->subWeek();
        
        // Get schedules that have passed but don't have reports yet
        $pendingSchedules = Schedule::with(['className.course', 'enrollment.student'])
            ->whereHas('teacherAssignment', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->whereDoesntHave('classReports')
            ->where('created_at', '<=', $oneWeekAgo)
            ->get();
            
        return $pendingSchedules;
    }
}