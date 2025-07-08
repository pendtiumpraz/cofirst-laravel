<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClassPhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classPhotos = ClassPhoto::with(['className', 'uploader'])
            ->latest()
            ->paginate(20);
        
        return view('admin.class-photos.index', compact('classPhotos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.class-photos.create')->with([
            'message' => 'Class Photos upload feature is coming soon!'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return redirect()->route('admin.class-photos.index')
            ->with('info', 'Class Photos upload feature is coming soon!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $classPhoto = ClassPhoto::with(['className', 'uploader'])->findOrFail($id);
        
        return view('admin.class-photos.show', compact('classPhoto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $classPhoto = ClassPhoto::with(['className', 'uploader'])->findOrFail($id);
        
        return view('admin.class-photos.edit', compact('classPhoto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $classPhoto = ClassPhoto::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_taken' => 'required|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
        ]);
        
        // Update basic fields
        $classPhoto->title = $request->title;
        $classPhoto->description = $request->description;
        $classPhoto->date_taken = $request->date_taken;
        
        // Handle photo upload if new photo is provided
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($classPhoto->photo_path) {
                Storage::disk('public')->delete($classPhoto->photo_path);
            }
            
            // Store new photo
            $file = $request->file('photo');
            $filename = uniqid() . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('class-photos', $filename, 'public');
            
            $classPhoto->photo_path = $path;
        }
        
        $classPhoto->save();
        
        return redirect()->route('admin.class-photos.show', $classPhoto)
            ->with('success', 'Class photo updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $classPhoto = ClassPhoto::findOrFail($id);
        
        // Delete the photo file if exists
        if ($classPhoto->photo_path) {
            Storage::disk('public')->delete($classPhoto->photo_path);
        }
        
        // Delete the record
        $classPhoto->delete();
        
        return redirect()->route('admin.class-photos.index')
            ->with('success', 'Class photo deleted successfully!');
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(string $id)
    {
        return redirect()->route('admin.class-photos.index')
            ->with('info', 'Class Photos status toggle feature is coming soon!');
    }
}
