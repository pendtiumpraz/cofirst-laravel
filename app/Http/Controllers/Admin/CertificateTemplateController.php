<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CertificateTemplate::withCount('certificates');
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }
        
        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortField, ['name', 'type', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }
        
        $templates = $query->paginate($request->get('per_page', 20))
                          ->appends($request->query());
            
        return view('admin.certificate-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = ['completion', 'achievement', 'participation'];
        $orientations = ['landscape', 'portrait'];
        $sizes = ['A4', 'Letter', 'Legal'];
        
        return view('admin.certificate-templates.create', compact('types', 'orientations', 'sizes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:completion,achievement,participation',
            'background_image' => 'nullable|image|max:5120', // 5MB max
            'content_template' => 'required|string',
            'orientation' => 'required|in:landscape,portrait',
            'size' => 'required|in:A4,Letter,Legal',
            'is_active' => 'boolean',
        ]);
        
        if ($request->hasFile('background_image')) {
            $validated['background_image'] = $request->file('background_image')->store('certificate-templates', 'public');
        }
        
        // Set default layout config
        $validated['layout_config'] = [
            'title' => ['x' => 50, 'y' => 20, 'fontSize' => 24, 'align' => 'center'],
            'content' => ['x' => 50, 'y' => 40, 'fontSize' => 14, 'align' => 'center'],
            'signature' => ['x' => 50, 'y' => 80, 'width' => 30, 'align' => 'center'],
            'date' => ['x' => 20, 'y' => 90, 'fontSize' => 12, 'align' => 'left'],
        ];
        
        // Set available variables based on type
        $validated['available_variables'] = CertificateTemplate::getDefaultVariables();
        
        $validated['is_active'] = $request->has('is_active');
        
        CertificateTemplate::create($validated);
        
        return redirect()->route('admin.certificate-templates.index')
            ->with('success', 'Certificate template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CertificateTemplate $certificateTemplate)
    {
        $certificateTemplate->load('certificates');
        return view('admin.certificate-templates.show', ['template' => $certificateTemplate]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CertificateTemplate $certificateTemplate)
    {
        $types = ['completion', 'achievement', 'participation'];
        $orientations = ['landscape', 'portrait'];
        $sizes = ['A4', 'Letter', 'Legal'];
        
        return view('admin.certificate-templates.edit', [
            'template' => $certificateTemplate,
            'types' => $types,
            'orientations' => $orientations,
            'sizes' => $sizes
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CertificateTemplate $certificateTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:completion,achievement,participation',
            'background_image' => 'nullable|image|max:5120', // 5MB max
            'content_template' => 'required|string',
            'orientation' => 'required|in:landscape,portrait',
            'size' => 'required|in:A4,Letter,Legal',
            'is_active' => 'boolean',
        ]);
        
        if ($request->hasFile('background_image')) {
            // Delete old image
            if ($certificateTemplate->background_image) {
                Storage::disk('public')->delete($certificateTemplate->background_image);
            }
            $validated['background_image'] = $request->file('background_image')->store('certificate-templates', 'public');
        }
        
        $validated['is_active'] = $request->has('is_active');
        
        $certificateTemplate->update($validated);
        
        return redirect()->route('admin.certificate-templates.index')
            ->with('success', 'Certificate template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CertificateTemplate $certificateTemplate)
    {
        if ($certificateTemplate->certificates()->count() > 0) {
            return back()->with('error', 'Cannot delete template that has been used for certificates.');
        }
        
        // Delete background image
        if ($certificateTemplate->background_image) {
            Storage::disk('public')->delete($certificateTemplate->background_image);
        }
        
        $certificateTemplate->delete();
        
        return redirect()->route('admin.certificate-templates.index')
            ->with('success', 'Certificate template deleted successfully.');
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(CertificateTemplate $certificateTemplate)
    {
        $certificateTemplate->is_active = !$certificateTemplate->is_active;
        $certificateTemplate->save();
        
        return back()->with('success', 'Certificate template status updated.');
    }
}