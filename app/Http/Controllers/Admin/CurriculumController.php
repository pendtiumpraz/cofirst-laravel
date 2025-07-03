<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CurriculumController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:admin|superadmin']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Curriculum::class);
        
        $curriculums = Curriculum::with(['course', 'syllabuses'])
                                ->withCount('syllabuses')
                                ->paginate(15);
        
        return view('admin.curriculums.index', compact('curriculums'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Curriculum::class);
        
        $courses = Course::where('status', 'active')->get();
        $types = Curriculum::getTypes();
        
        return view('admin.curriculums.create', compact('courses', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Curriculum::class);
        
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:fast-track,regular,expert,beginner',
            'status' => 'required|in:active,inactive',
            'duration_weeks' => 'nullable|integer|min:1|max:52',
            'objectives' => 'nullable|string',
        ]);
        
        $curriculum = Curriculum::create($validated);
        
        return redirect()->route('admin.curriculums.index')
                        ->with('success', 'Kurikulum berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Curriculum $curriculum)
    {
        $this->authorize('view', $curriculum);
        
        $curriculum->load([
            'course',
            'syllabuses' => function($query) {
                $query->orderBy('meeting_number');
            },
            'syllabuses.materials' => function($query) {
                $query->orderBy('order');
            }
        ]);
        
        return view('admin.curriculums.show', compact('curriculum'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Curriculum $curriculum)
    {
        $this->authorize('update', $curriculum);
        
        $courses = Course::where('status', 'active')->get();
        $types = Curriculum::getTypes();
        
        return view('admin.curriculums.edit', compact('curriculum', 'courses', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Curriculum $curriculum)
    {
        $this->authorize('update', $curriculum);
        
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:fast-track,regular,expert,beginner',
            'status' => 'required|in:active,inactive',
            'duration_weeks' => 'nullable|integer|min:1|max:52',
            'objectives' => 'nullable|string',
        ]);
        
        $curriculum->update($validated);
        
        return redirect()->route('admin.curriculums.index')
                        ->with('success', 'Kurikulum berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Curriculum $curriculum)
    {
        $this->authorize('delete', $curriculum);
        
        // Check if curriculum has syllabuses
        if ($curriculum->syllabuses()->count() > 0) {
            return redirect()->route('admin.curriculums.index')
                           ->with('error', 'Tidak dapat menghapus kurikulum yang memiliki silabus.');
        }
        
        $curriculum->delete();
        
        return redirect()->route('admin.curriculums.index')
                        ->with('success', 'Kurikulum berhasil dihapus.');
    }

    /**
     * Toggle curriculum status
     */
    public function toggleStatus(Curriculum $curriculum)
    {
        $this->authorize('update', $curriculum);
        
        $curriculum->update([
            'status' => $curriculum->status === 'active' ? 'inactive' : 'active'
        ]);
        
        $status = $curriculum->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->back()
                        ->with('success', "Kurikulum berhasil {$status}.");
    }
}