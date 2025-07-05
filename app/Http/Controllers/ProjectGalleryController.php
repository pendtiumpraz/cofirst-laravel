<?php

namespace App\Http\Controllers;

use App\Models\ProjectGallery;
use App\Models\ClassName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProjectGalleryController extends Controller
{
    /**
     * Display a listing of the project galleries.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = ProjectGallery::with(['student', 'class']);

        // Filter based on user role
        if ($user->hasRole('student')) {
            $query->where('student_id', $user->id);
        } elseif ($user->hasRole('teacher')) {
            $classIds = $user->teachingClasses->pluck('id');
            $query->whereIn('class_id', $classIds);
        } elseif ($user->hasRole('parent')) {
            $childrenIds = $user->children->pluck('id');
            $query->whereIn('student_id', $childrenIds);
        }

        $galleries = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('project-gallery.index', compact('galleries'));
    }

    /**
     * Show the form for creating a new project gallery.
     */
    public function create()
    {
        $user = auth()->user();
        $classes = $user->enrollments()->with('class')->get()->pluck('class');
        
        return view('project-gallery.create', compact('classes'));
    }

    /**
     * Store a newly created project gallery in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:class_names,id',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'is_featured' => 'boolean'
        ]);

        $user = $request->user();

        // Verify user is enrolled in the class
        if (!$user->enrollments()->where('class_id', $request->class_id)->exists()) {
            return back()->with('error', 'You are not enrolled in this class.');
        }

        // Store photo
        $photo = $request->file('photo');
        $filename = 'project-galleries/' . $user->id . '-' . time() . '.' . $photo->getClientOriginalExtension();
        
        // Create thumbnail
        $thumbnailFilename = 'project-galleries/thumbnails/' . $user->id . '-' . time() . '.' . $photo->getClientOriginalExtension();
        
        // Process main image
        $image = Image::make($photo);
        $image->resize(1200, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        Storage::disk('public')->put($filename, $image->encode());
        
        // Process thumbnail
        $thumbnail = Image::make($photo);
        $thumbnail->fit(400, 300);
        Storage::disk('public')->put($thumbnailFilename, $thumbnail->encode());

        // Create gallery entry
        $gallery = ProjectGallery::create([
            'student_id' => $user->id,
            'class_id' => $request->class_id,
            'title' => $request->title,
            'description' => $request->description,
            'photo_path' => $filename,
            'thumbnail_path' => $thumbnailFilename,
            'is_featured' => $request->boolean('is_featured', false)
        ]);

        return redirect()->route('project-gallery.show', $gallery)
            ->with('success', 'Project uploaded successfully!');
    }

    /**
     * Display the specified project gallery.
     */
    public function show(ProjectGallery $projectGallery)
    {
        $projectGallery->load(['student', 'class']);
        $projectGallery->incrementViews();
        
        // Get related projects
        $relatedProjects = ProjectGallery::where('class_id', $projectGallery->class_id)
            ->where('id', '!=', $projectGallery->id)
            ->limit(4)
            ->get();

        return view('project-gallery.show', compact('projectGallery', 'relatedProjects'));
    }

    /**
     * Show the form for editing the specified project gallery.
     */
    public function edit(ProjectGallery $projectGallery)
    {
        $this->authorize('update', $projectGallery);
        
        $user = auth()->user();
        $classes = $user->enrollments()->with('class')->get()->pluck('class');
        
        return view('project-gallery.edit', compact('projectGallery', 'classes'));
    }

    /**
     * Update the specified project gallery in storage.
     */
    public function update(Request $request, ProjectGallery $projectGallery)
    {
        $this->authorize('update', $projectGallery);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:class_names,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'is_featured' => 'boolean'
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'class_id' => $request->class_id,
            'is_featured' => $request->boolean('is_featured', false)
        ];

        // Handle photo update if provided
        if ($request->hasFile('photo')) {
            // Delete old photos
            Storage::disk('public')->delete($projectGallery->photo_path);
            Storage::disk('public')->delete($projectGallery->thumbnail_path);

            // Store new photo
            $photo = $request->file('photo');
            $filename = 'project-galleries/' . auth()->id() . '-' . time() . '.' . $photo->getClientOriginalExtension();
            $thumbnailFilename = 'project-galleries/thumbnails/' . auth()->id() . '-' . time() . '.' . $photo->getClientOriginalExtension();
            
            // Process images
            $image = Image::make($photo);
            $image->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            Storage::disk('public')->put($filename, $image->encode());
            
            $thumbnail = Image::make($photo);
            $thumbnail->fit(400, 300);
            Storage::disk('public')->put($thumbnailFilename, $thumbnail->encode());

            $data['photo_path'] = $filename;
            $data['thumbnail_path'] = $thumbnailFilename;
        }

        $projectGallery->update($data);

        return redirect()->route('project-gallery.show', $projectGallery)
            ->with('success', 'Project updated successfully!');
    }

    /**
     * Remove the specified project gallery from storage.
     */
    public function destroy(ProjectGallery $projectGallery)
    {
        $this->authorize('delete', $projectGallery);

        // Delete photos
        Storage::disk('public')->delete($projectGallery->photo_path);
        Storage::disk('public')->delete($projectGallery->thumbnail_path);

        $projectGallery->delete();

        return redirect()->route('project-gallery.index')
            ->with('success', 'Project deleted successfully!');
    }

    /**
     * Display featured projects
     */
    public function featured()
    {
        $featuredProjects = ProjectGallery::with(['student', 'class'])
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('project-gallery.featured', compact('featuredProjects'));
    }
}