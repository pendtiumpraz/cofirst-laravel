<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Syllabus;
use App\Models\Curriculum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SyllabusController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:admin|superadmin']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Syllabus::class);
        
        $query = Syllabus::with(['curriculum.course', 'materials'])
                         ->withCount('materials');
        
        // Filter by curriculum if provided
        if ($request->filled('curriculum_id')) {
            $query->where('curriculum_id', $request->curriculum_id);
        }
        
        $syllabuses = $query->orderBy('curriculum_id')
                           ->orderBy('meeting_number')
                           ->paginate(15);
        
        $curriculums = Curriculum::with('course')->active()->get();
        
        return view('admin.syllabuses.index', compact('syllabuses', 'curriculums'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Syllabus::class);
        
        $curriculums = Curriculum::with('course')->active()->get();
        $selectedCurriculumId = $request->get('curriculum_id');
        
        // Get next meeting number for selected curriculum
        $nextMeetingNumber = 1;
        if ($selectedCurriculumId) {
            $lastSyllabus = Syllabus::where('curriculum_id', $selectedCurriculumId)
                                  ->orderBy('meeting_number', 'desc')
                                  ->first();
            $nextMeetingNumber = $lastSyllabus ? $lastSyllabus->meeting_number + 1 : 1;
        }
        
        return view('admin.syllabuses.create', compact('curriculums', 'selectedCurriculumId', 'nextMeetingNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Syllabus::class);
        
        $validated = $request->validate([
            'curriculum_id' => 'required|exists:curriculums,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'meeting_number' => 'required|integer|min:1',
            'learning_objectives' => 'nullable|string',
            'activities' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:30|max:480',
            'status' => 'required|in:active,inactive',
        ]);
        
        // Check if meeting number already exists for this curriculum
        $existingSyllabus = Syllabus::where('curriculum_id', $validated['curriculum_id'])
                                  ->where('meeting_number', $validated['meeting_number'])
                                  ->first();
        
        if ($existingSyllabus) {
            return redirect()->back()
                           ->withInput()
                           ->withErrors(['meeting_number' => 'Nomor pertemuan sudah ada untuk kurikulum ini.']);
        }
        
        $syllabus = Syllabus::create($validated);
        
        return redirect()->route('admin.syllabuses.index')
                        ->with('success', 'Silabus berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Syllabus $syllabus)
    {
        $this->authorize('view', $syllabus);
        
        $syllabus->load([
            'curriculum.course',
            'materials' => function($query) {
                $query->orderBy('order');
            }
        ]);
        
        return view('admin.syllabuses.show', compact('syllabus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Syllabus $syllabus)
    {
        $this->authorize('update', $syllabus);
        
        $curriculums = Curriculum::with('course')->active()->get();
        
        return view('admin.syllabuses.edit', compact('syllabus', 'curriculums'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Syllabus $syllabus)
    {
        $this->authorize('update', $syllabus);
        
        $validated = $request->validate([
            'curriculum_id' => 'required|exists:curriculums,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'meeting_number' => 'required|integer|min:1',
            'learning_objectives' => 'nullable|string',
            'activities' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:30|max:480',
            'status' => 'required|in:active,inactive',
        ]);
        
        // Check if meeting number already exists for this curriculum (excluding current syllabus)
        $existingSyllabus = Syllabus::where('curriculum_id', $validated['curriculum_id'])
                                  ->where('meeting_number', $validated['meeting_number'])
                                  ->where('id', '!=', $syllabus->id)
                                  ->first();
        
        if ($existingSyllabus) {
            return redirect()->back()
                           ->withInput()
                           ->withErrors(['meeting_number' => 'Nomor pertemuan sudah ada untuk kurikulum ini.']);
        }
        
        $syllabus->update($validated);
        
        return redirect()->route('admin.syllabuses.index')
                        ->with('success', 'Silabus berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Syllabus $syllabus)
    {
        $this->authorize('delete', $syllabus);
        
        // Check if syllabus has materials
        if ($syllabus->materials()->count() > 0) {
            return redirect()->route('admin.syllabuses.index')
                           ->with('error', 'Tidak dapat menghapus silabus yang memiliki materi.');
        }
        
        $syllabus->delete();
        
        return redirect()->route('admin.syllabuses.index')
                        ->with('success', 'Silabus berhasil dihapus.');
    }

    /**
     * Toggle syllabus status
     */
    public function toggleStatus(Syllabus $syllabus)
    {
        $this->authorize('update', $syllabus);
        
        $syllabus->update([
            'status' => $syllabus->status === 'active' ? 'inactive' : 'active'
        ]);
        
        $status = $syllabus->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->back()
                        ->with('success', "Silabus berhasil {$status}.");
    }

    /**
     * Get syllabuses for a specific curriculum (AJAX)
     */
    public function getByCurriculum(Request $request)
    {
        $curriculumId = $request->get('curriculum_id');
        
        if (!$curriculumId) {
            return response()->json([]);
        }
        
        $syllabuses = Syllabus::where('curriculum_id', $curriculumId)
                             ->where('status', 'active')
                             ->orderBy('meeting_number')
                             ->get(['id', 'title', 'meeting_number']);
        
        return response()->json($syllabuses);
    }
}