<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\ClassProgress;
use App\Models\MaterialAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:parent']);
    }

    /**
     * Display progress overview for all children
     */
    public function index()
    {
        $parent = Auth::user();
        
        // Get all children with their enrollments and progress
        $children = $parent->children()
                          ->with([
                              'enrollments' => function($query) {
                                  $query->where('status', 'active')
                                        ->with('class.curriculum.course');
                              }
                          ])
                          ->get();
        
        // Calculate progress for each child
        $progressData = [];
        foreach ($children as $child) {
            $childProgress = [];
            foreach ($child->enrollments as $enrollment) {
                $classId = $enrollment->class_id;
                $totalMeetings = $enrollment->class->curriculum->syllabuses()->count();
                $completedMeetings = ClassProgress::where('student_id', $child->id)
                                                 ->where('class_id', $classId)
                                                 ->where('status', 'completed')
                                                 ->count();
                
                $childProgress[$classId] = [
                    'class' => $enrollment->class,
                    'total' => $totalMeetings,
                    'completed' => $completedMeetings,
                    'percentage' => $totalMeetings > 0 ? round(($completedMeetings / $totalMeetings) * 100, 2) : 0
                ];
            }
            $progressData[$child->id] = $childProgress;
        }
        
        return view('parent.progress.index', compact('children', 'progressData'));
    }

    /**
     * Display detailed progress for a specific child
     */
    public function child($childId)
    {
        $parent = Auth::user();
        
        // Check if this child belongs to the parent
        $child = $parent->children()->findOrFail($childId);
        
        // Get child's enrollments with detailed progress
        $enrollments = Enrollment::where('student_id', $child->id)
                                ->where('status', 'active')
                                ->with([
                                    'class.curriculum.course',
                                    'class.curriculum.syllabuses' => function($query) {
                                        $query->where('status', 'active')->orderBy('meeting_number');
                                    }
                                ])
                                ->get();
        
        // Get detailed progress for each enrollment
        $detailedProgress = [];
        foreach ($enrollments as $enrollment) {
            $classId = $enrollment->class_id;
            
            // Get all progress records for this class
            $progress = ClassProgress::where('student_id', $child->id)
                                   ->where('class_id', $classId)
                                   ->with(['syllabus', 'material'])
                                   ->orderBy('meeting_number')
                                   ->get();
            
            // Get material access history
            $materialAccess = MaterialAccess::where('student_id', $child->id)
                                           ->whereHas('material.syllabus', function($query) use ($enrollment) {
                                               $query->where('curriculum_id', $enrollment->class->curriculum_id);
                                           })
                                           ->with('material.syllabus')
                                           ->latest('accessed_at')
                                           ->get();
            
            $detailedProgress[$classId] = [
                'class' => $enrollment->class,
                'progress' => $progress,
                'material_access' => $materialAccess,
                'total_meetings' => $enrollment->class->curriculum->syllabuses->count(),
                'completed_meetings' => $progress->where('status', 'completed')->count(),
                'in_progress_meetings' => $progress->where('status', 'in_progress')->count()
            ];
        }
        
        return view('parent.progress.child', compact('child', 'detailedProgress'));
    }

    /**
     * Display materials accessed by a specific child in a class
     */
    public function childMaterials($childId, $classId)
    {
        $parent = Auth::user();
        
        // Check if this child belongs to the parent
        $child = $parent->children()->findOrFail($childId);
        
        // Check if child is enrolled in this class
        $enrollment = Enrollment::where('student_id', $child->id)
                               ->where('class_id', $classId)
                               ->where('status', 'active')
                               ->with('class.curriculum.course')
                               ->firstOrFail();
        
        // Get completed meetings
        $completedMeetings = ClassProgress::where('student_id', $child->id)
                                         ->where('class_id', $classId)
                                         ->where('status', 'completed')
                                         ->pluck('meeting_number')
                                         ->toArray();
        
        // Get available materials (only for completed meetings)
        $availableMaterials = collect();
        $curriculum = $enrollment->class->curriculum;
        
        foreach ($curriculum->syllabuses()->where('status', 'active')->orderBy('meeting_number')->get() as $syllabus) {
            if (in_array($syllabus->meeting_number, $completedMeetings)) {
                $materials = $syllabus->materials()->where('status', 'active')->orderBy('order')->get();
                foreach ($materials as $material) {
                    $material->syllabus_info = $syllabus;
                    $availableMaterials->push($material);
                }
            }
        }
        
        // Get material access history for this child
        $accessHistory = MaterialAccess::where('student_id', $child->id)
                                      ->whereIn('material_id', $availableMaterials->pluck('id'))
                                      ->with('material.syllabus')
                                      ->latest('accessed_at')
                                      ->get();
        
        // Group access by material
        $materialStats = [];
        foreach ($availableMaterials as $material) {
            $accesses = $accessHistory->where('material_id', $material->id);
            $materialStats[$material->id] = [
                'material' => $material,
                'access_count' => $accesses->count(),
                'total_time' => $accesses->sum('access_duration_seconds'),
                'last_access' => $accesses->first()?->accessed_at,
                'first_access' => $accesses->last()?->accessed_at
            ];
        }
        
        return view('parent.progress.child-materials', compact(
            'child', 'enrollment', 'materialStats', 'completedMeetings'
        ));
    }

    /**
     * Get progress summary for a child (AJAX)
     */
    public function getChildProgressSummary($childId)
    {
        $parent = Auth::user();
        
        // Check if this child belongs to the parent
        $child = $parent->children()->findOrFail($childId);
        
        $enrollments = Enrollment::where('student_id', $child->id)
                                ->where('status', 'active')
                                ->with('class.curriculum.course')
                                ->get();
        
        $summary = [];
        foreach ($enrollments as $enrollment) {
            $classId = $enrollment->class_id;
            $totalMeetings = $enrollment->class->curriculum->syllabuses()->count();
            $completedMeetings = ClassProgress::where('student_id', $child->id)
                                            ->where('class_id', $classId)
                                            ->where('status', 'completed')
                                            ->count();
            
            $summary[] = [
                'class_name' => $enrollment->class->name,
                'course_name' => $enrollment->class->course->name,
                'total_meetings' => $totalMeetings,
                'completed_meetings' => $completedMeetings,
                'percentage' => $totalMeetings > 0 ? round(($completedMeetings / $totalMeetings) * 100, 2) : 0,
                'status' => $this->getProgressStatus($completedMeetings, $totalMeetings)
            ];
        }
        
        return response()->json([
            'child_name' => $child->name,
            'classes' => $summary
        ]);
    }

    /**
     * Get progress status based on completion percentage
     */
    private function getProgressStatus($completed, $total)
    {
        if ($total === 0) {
            return 'not_started';
        }
        
        $percentage = ($completed / $total) * 100;
        
        if ($percentage === 100) {
            return 'completed';
        } elseif ($percentage >= 75) {
            return 'almost_done';
        } elseif ($percentage >= 50) {
            return 'halfway';
        } elseif ($percentage >= 25) {
            return 'getting_started';
        } else {
            return 'just_started';
        }
    }
}