<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\User;
use App\Models\ClassName;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Display a listing of reports for admin.
     */
    public function adminIndex()
    {
        $reports = Report::with(['student', 'teacher', 'className.course'])
            ->latest()
            ->paginate(10);
            
        return view('admin.reports.index', compact('reports'));
    }

    /**
     * Display a listing of reports for teacher.
     */
    public function index()
    {
        $user = Auth::user();
        $reports = Report::where('teacher_id', $user->id)
            ->with(['student', 'class.course'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('teacher.reports.index', compact('reports'));
    }

    /**
     * Show the form for creating a new report.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get classes taught by this teacher
        $classes = ClassName::whereHas('teachers', function($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->where('is_active', true)
            ->with('course')
            ->get();
            
        // Get students from teacher's classes
        $students = User::whereHas('enrollments.class.teachers', function($query) use ($user) {
            $query->where('users.id', $user->id)->where('status', 'active');
        })->get();

        \Log::info('Teacher Report Create: Data for view', [
            'teacher_id' => $user->id,
            'classes_count' => $classes->count(),
            'students_count' => $students->count(),
        ]);
        
        return view('teacher.reports.create', compact('classes', 'students'));
    }

    /**
     * Store a newly created report in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:class_names,id',
            'report_type' => 'required|in:weekly,monthly,final,incident',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'score' => 'nullable|numeric|min:0|max:100'
        ]);

        $user = Auth::user();
        
        // Verify teacher is assigned to this class
        $class = ClassName::findOrFail($request->class_id);
        if (!$class->teachers->contains($user->id)) {
            abort(403, 'Unauthorized to create report for this class.');
        }

        Report::create([
            'student_id' => $request->student_id,
            'teacher_id' => $user->id,
            'class_id' => $request->class_id,
            'report_type' => $request->report_type,
            'title' => $request->title,
            'content' => $request->content,
            'score' => $request->score,
            'report_date' => now(),
        ]);

        return redirect()->route('teacher.reports.index')->with('success', 'Report created successfully.');
    }

    /**
     * Display the specified report.
     */
    public function show(Report $report)
    {
        $user = Auth::user();
        
        // Check authorization
        if ($user->hasRole('teacher') && $report->teacher_id !== $user->id) {
            abort(403, 'Unauthorized access to this report.');
        }
        
        $report->load(['student', 'teacher', 'class.course']);
        
        return view('teacher.reports.show', compact('report'));
    }

    /**
     * Show the form for editing the specified report.
     */
    public function edit(Report $report)
    {
        $user = Auth::user();
        
        // Verify teacher owns this report
        if ($report->teacher_id !== $user->id) {
            abort(403, 'Unauthorized to edit this report.');
        }
        
        $classes = ClassName::whereHas('teachers', function($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->where('is_active', true)
            ->with('course')
            ->get();
            
        $students = User::whereHas('enrollments.class.teachers', function($query) use ($user) {
            $query->where('users.id', $user->id)->where('status', 'active');
        })->get();

        \Log::info('Teacher Report Edit: Data for view', [
            'report_id' => $report->id,
            'teacher_id' => $user->id,
            'classes_count' => $classes->count(),
            'students_count' => $students->count(),
        ]);
        
        return view('teacher.reports.edit', compact('report', 'classes', 'students'));
    }

    /**
     * Update the specified report in storage.
     */
    public function update(Request $request, Report $report)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:class_names,id',
            'report_type' => 'required|in:weekly,monthly,final,incident',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'score' => 'nullable|numeric|min:0|max:100'
        ]);

        $user = Auth::user();
        
        // Verify teacher owns this report
        if ($report->teacher_id !== $user->id) {
            abort(403, 'Unauthorized to update this report.');
        }

        $report->update([
            'student_id' => $request->student_id,
            'class_id' => $request->class_id,
            'report_type' => $request->report_type,
            'title' => $request->title,
            'content' => $request->content,
            'score' => $request->score,
        ]);

        return redirect()->route('teacher.reports.index')->with('success', 'Report updated successfully.');
    }

    /**
     * Remove the specified report from storage.
     */
    public function destroy(Report $report)
    {
        $user = Auth::user();
        
        // Verify teacher owns this report
        if ($report->teacher_id !== $user->id) {
            abort(403, 'Unauthorized to delete this report.');
        }

        $report->delete();
        return redirect()->route('teacher.reports.index')->with('success', 'Report deleted successfully.');
    }

    /**
     * Display reports by class.
     */
    public function byClass(ClassName $class)
    {
        $user = Auth::user();
        
        // Verify teacher is assigned to this class
        if (!$class->teachers->contains($user->id)) {
            abort(403, 'Unauthorized access to class reports.');
        }
        
        $reports = Report::where('class_id', $class->id)
            ->with(['student', 'teacher'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('teacher.reports.by-class', compact('class', 'reports'));
    }

    /**
     * Display reports by student.
     */
    public function byStudent(User $student)
    {
        $user = Auth::user();
        
        // Verify teacher has access to this student
        if (!$student->enrollments()->whereHas('class.teachers', function($query) use ($user) {
            $query->where('users.id', $user->id);
        })->exists()) {
            abort(403, 'Unauthorized access to student reports.');
        }
        
        $reports = Report::where('student_id', $student->id)
            ->where('teacher_id', $user->id)
            ->with(['class.course'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('teacher.reports.by-student', compact('student', 'reports'));
    }
}
