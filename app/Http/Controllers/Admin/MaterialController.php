<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Syllabus;
use App\Models\Curriculum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class MaterialController extends Controller
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
        $this->authorize('viewAny', Material::class);
        
        $query = Material::with(['syllabus.curriculum.course']);
        
        // Filter by curriculum if provided
        if ($request->filled('curriculum_id')) {
            $query->whereHas('syllabus', function($q) use ($request) {
                $q->where('curriculum_id', $request->curriculum_id);
            });
        }
        
        // Filter by syllabus if provided
        if ($request->filled('syllabus_id')) {
            $query->where('syllabus_id', $request->syllabus_id);
        }
        
        $materials = $query->orderBy('syllabus_id')
                          ->orderBy('order')
                          ->paginate(15);
        
        $curriculums = Curriculum::with('course')->active()->get();
        $syllabuses = collect();
        
        if ($request->filled('curriculum_id')) {
            $syllabuses = Syllabus::where('curriculum_id', $request->curriculum_id)
                                 ->active()
                                 ->orderBy('meeting_number')
                                 ->get();
        }
        
        return view('admin.materials.index', compact('materials', 'curriculums', 'syllabuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Material::class);
        
        $curriculums = Curriculum::with('course')->active()->get();
        $syllabuses = collect();
        $selectedCurriculumId = $request->get('curriculum_id');
        $selectedSyllabusId = $request->get('syllabus_id');
        
        if ($selectedCurriculumId) {
            $syllabuses = Syllabus::where('curriculum_id', $selectedCurriculumId)
                                 ->active()
                                 ->orderBy('meeting_number')
                                 ->get();
        }
        
        // Get next order number for selected syllabus
        $nextOrder = 1;
        if ($selectedSyllabusId) {
            $lastMaterial = Material::where('syllabus_id', $selectedSyllabusId)
                                  ->orderBy('order', 'desc')
                                  ->first();
            $nextOrder = $lastMaterial ? $lastMaterial->order + 1 : 1;
        }
        
        $types = Material::getTypes();
        
        return view('admin.materials.create', compact(
            'curriculums', 'syllabuses', 'selectedCurriculumId', 
            'selectedSyllabusId', 'nextOrder', 'types'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Material::class);
        
        $validated = $request->validate([
            'syllabus_id' => 'required|exists:syllabuses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'meeting_start' => 'required|integer|min:1',
            'meeting_end' => 'required|integer|min:1|gte:meeting_start',
            'type' => 'required|in:document,video,exercise,quiz,project',
            'external_url' => 'nullable|url',
            'status' => 'required|in:active,inactive',
            'order' => 'required|integer|min:1',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,mp4,avi,mov,jpg,jpeg,png|max:51200', // 50MB max
        ]);
        
        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('materials', $filename, 'public');
            $validated['file_path'] = $path;
        }
        
        $material = Material::create($validated);
        
        return redirect()->route('admin.materials.index')
                        ->with('success', 'Materi berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Material $material)
    {
        $this->authorize('view', $material);
        
        $material->load('syllabus.curriculum.course');
        
        return view('admin.materials.show', compact('material'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Material $material)
    {
        $this->authorize('update', $material);
        
        $curriculums = Curriculum::with('course')->active()->get();
        $syllabuses = Syllabus::where('curriculum_id', $material->syllabus->curriculum_id)
                             ->active()
                             ->orderBy('meeting_number')
                             ->get();
        $types = Material::getTypes();
        
        return view('admin.materials.edit', compact('material', 'curriculums', 'syllabuses', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Material $material)
    {
        $this->authorize('update', $material);
        
        $validated = $request->validate([
            'syllabus_id' => 'required|exists:syllabuses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'meeting_start' => 'required|integer|min:1',
            'meeting_end' => 'required|integer|min:1|gte:meeting_start',
            'type' => 'required|in:document,video,exercise,quiz,project',
            'external_url' => 'nullable|url',
            'status' => 'required|in:active,inactive',
            'order' => 'required|integer|min:1',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,mp4,avi,mov,jpg,jpeg,png|max:51200', // 50MB max
        ]);
        
        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }
            
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('materials', $filename, 'public');
            $validated['file_path'] = $path;
        }
        
        $material->update($validated);
        
        return redirect()->route('admin.materials.index')
                        ->with('success', 'Materi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Material $material)
    {
        $this->authorize('delete', $material);
        
        // Delete associated file if exists
        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }
        
        $material->delete();
        
        return redirect()->route('admin.materials.index')
                        ->with('success', 'Materi berhasil dihapus.');
    }

    /**
     * Toggle material status
     */
    public function toggleStatus(Material $material)
    {
        $this->authorize('update', $material);
        
        $material->update([
            'status' => $material->status === 'active' ? 'inactive' : 'active'
        ]);
        
        $status = $material->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->back()
                        ->with('success', "Materi berhasil {$status}.");
    }

    /**
     * Download material file
     */
    public function download(Material $material)
    {
        $this->authorize('view', $material);
        
        if (!$material->file_path || !Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }
        
        return Storage::disk('public')->download($material->file_path, $material->title);
    }

    /**
     * Get materials for a specific syllabus (AJAX)
     */
    public function getBySyllabus(Request $request)
    {
        $syllabusId = $request->get('syllabus_id');
        
        if (!$syllabusId) {
            return response()->json([]);
        }
        
        $materials = Material::where('syllabus_id', $syllabusId)
                            ->where('status', 'active')
                            ->orderBy('order')
                            ->get(['id', 'title', 'meeting_start', 'meeting_end', 'type']);
        
        return response()->json($materials);
    }
}