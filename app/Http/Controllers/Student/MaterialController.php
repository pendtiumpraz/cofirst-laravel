<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialAccess;
use App\Models\ClassProgress;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:student']);
    }

    /**
     * Display materials for student's enrolled classes
     */
    public function index()
    {
        $student = Auth::user();
        
        // Get student's active enrollments with class progress
        $enrollments = Enrollment::where('student_id', $student->id)
                                ->where('status', 'active')
                                ->with([
                                    'class.curriculum.syllabuses' => function($query) {
                                        $query->where('status', 'active')->orderBy('meeting_number');
                                    },
                                    'class.curriculum.syllabuses.materials' => function($query) {
                                        $query->where('status', 'active')->orderBy('order');
                                    }
                                ])
                                ->get();
        
        // Get progress for each enrollment
        $progressData = [];
        foreach ($enrollments as $enrollment) {
            $classId = $enrollment->class_id;
            $totalMeetings = $enrollment->class->curriculum->syllabuses->count();
            $completedMeetings = ClassProgress::where('student_id', $student->id)
                                            ->where('class_id', $classId)
                                            ->where('status', 'completed')
                                            ->count();
            
            $progressData[$classId] = [
                'total' => $totalMeetings,
                'completed' => $completedMeetings,
                'percentage' => $totalMeetings > 0 ? round(($completedMeetings / $totalMeetings) * 100, 2) : 0
            ];
        }
        
        return view('student.materials.index', compact('enrollments', 'progressData'));
    }

    /**
     * Display materials for a specific class
     */
    public function byClass($classId)
    {
        $student = Auth::user();
        
        // Check if student is enrolled in this class
        $enrollment = Enrollment::where('student_id', $student->id)
                               ->where('class_id', $classId)
                               ->where('status', 'active')
                               ->with('class.curriculum.syllabuses.materials')
                               ->first();
        
        if (!$enrollment) {
            abort(403, 'Anda tidak terdaftar di kelas ini.');
        }
        
        $class = $enrollment->class;
        
        // Get completed meetings for this student
        $completedMeetings = ClassProgress::where('student_id', $student->id)
                                         ->where('class_id', $classId)
                                         ->where('status', 'completed')
                                         ->pluck('meeting_number')
                                         ->toArray();
        
        // Get available materials (only for completed meetings)
        $availableMaterials = collect();
        foreach ($class->curriculum->syllabuses as $syllabus) {
            if (in_array($syllabus->meeting_number, $completedMeetings)) {
                $materials = $syllabus->materials->where('status', 'active');
                foreach ($materials as $material) {
                    $availableMaterials->push($material);
                }
            }
        }
        
        // Get material access history
        $accessHistory = MaterialAccess::where('student_id', $student->id)
                                      ->whereIn('material_id', $availableMaterials->pluck('id'))
                                      ->with('material')
                                      ->latest('accessed_at')
                                      ->get();
        
        return view('student.materials.by-class', compact(
            'class', 'availableMaterials', 'completedMeetings', 'accessHistory'
        ));
    }

    /**
     * Display a specific material
     */
    public function show(Material $material)
    {
        $student = Auth::user();
        
        // Check if student has access to this material
        $hasAccess = $this->checkMaterialAccess($student->id, $material);
        
        if (!$hasAccess) {
            abort(403, 'Anda belum dapat mengakses materi ini. Materi hanya tersedia setelah pertemuan selesai.');
        }
        
        // Record material access
        MaterialAccess::recordAccess($student->id, $material->id);
        
        $material->load('syllabus.curriculum.course');
        
        // Get access statistics
        $totalAccessTime = MaterialAccess::getTotalAccessTime($student->id, $material->id);
        $accessCount = MaterialAccess::getAccessCount($student->id, $material->id);
        $lastAccess = MaterialAccess::getLastAccessTime($student->id, $material->id);
        
        return view('student.materials.show', compact(
            'material', 'totalAccessTime', 'accessCount', 'lastAccess'
        ));
    }

    /**
     * Download material file
     */
    public function download(Material $material)
    {
        $student = Auth::user();
        
        // Check if student has access to this material
        $hasAccess = $this->checkMaterialAccess($student->id, $material);
        
        if (!$hasAccess) {
            abort(403, 'Anda belum dapat mengakses materi ini.');
        }
        
        if (!$material->file_path || !Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }
        
        // Record material access
        MaterialAccess::recordAccess($student->id, $material->id);
        
        return Storage::disk('public')->download($material->file_path, $material->title);
    }

    /**
     * Get student progress for a specific class
     */
    public function progress($classId)
    {
        $student = Auth::user();
        
        // Check if student is enrolled in this class
        $enrollment = Enrollment::where('student_id', $student->id)
                               ->where('class_id', $classId)
                               ->where('status', 'active')
                               ->with('class.curriculum.syllabuses')
                               ->first();
        
        if (!$enrollment) {
            abort(403, 'Anda tidak terdaftar di kelas ini.');
        }
        
        $class = $enrollment->class;
        
        // Get all progress for this student in this class
        $progress = ClassProgress::where('student_id', $student->id)
                                ->where('class_id', $classId)
                                ->with(['syllabus', 'material'])
                                ->orderBy('meeting_number')
                                ->get();
        
        // Calculate progress statistics
        $totalMeetings = $class->curriculum->syllabuses->count();
        $completedMeetings = $progress->where('status', 'completed')->count();
        $inProgressMeetings = $progress->where('status', 'in_progress')->count();
        $progressPercentage = $totalMeetings > 0 ? round(($completedMeetings / $totalMeetings) * 100, 2) : 0;
        
        return view('student.materials.progress', compact(
            'class', 'progress', 'totalMeetings', 'completedMeetings', 
            'inProgressMeetings', 'progressPercentage'
        ));
    }

    /**
     * Check if student has access to a material
     */
    private function checkMaterialAccess($studentId, Material $material)
    {
        // Get student's enrollments in classes that use this curriculum
        $enrollments = Enrollment::where('student_id', $studentId)
                                ->where('status', 'active')
                                ->whereHas('class', function($query) use ($material) {
                                    $query->where('curriculum_id', $material->syllabus->curriculum_id);
                                })
                                ->get();
        
        if ($enrollments->isEmpty()) {
            return false;
        }
        
        // Check if any of the meetings for this material have been completed
        foreach ($enrollments as $enrollment) {
            $completedMeetings = ClassProgress::where('student_id', $studentId)
                                            ->where('class_id', $enrollment->class_id)
                                            ->where('status', 'completed')
                                            ->pluck('meeting_number')
                                            ->toArray();
            
            // Check if material is available for any completed meeting
            for ($meeting = $material->meeting_start; $meeting <= $material->meeting_end; $meeting++) {
                if (in_array($meeting, $completedMeetings)) {
                    return true;
                }
            }
        }
        
        return false;
    }
}