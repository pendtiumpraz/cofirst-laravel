<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Syllabus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:teacher']);
    }

    /**
     * Display a listing of materials for teacher's classes
     */
    public function index()
    {
        $teacher = Auth::user();
        
        // Get materials from teacher's assigned classes
        $materials = Material::whereHas('syllabus.curriculum.course.classes.teachers', function($query) use ($teacher) {
            $query->where('users.id', $teacher->id);
        })
        ->with([
            'syllabus.curriculum.course'
        ])
        ->where('status', 'active')
        ->orderBy('order')
        ->get();
        
        return view('teacher.materials.index', compact('materials'));
    }

    /**
     * Display the specified material
     */
    public function show(Material $material)
    {
        $teacher = Auth::user();
        
        // Check if teacher has access to this material (via assigned classes)
        $hasAccess = $material->syllabus->curriculum->course->classes()
                                ->whereHas('teachers', function($query) use ($teacher) {
                                    $query->where('users.id', $teacher->id);
                                })
                                ->where('status', 'active')
                                ->exists();
        
        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke materi ini.');
        }
        
        $material->load([
            'syllabus.curriculum.course'
        ]);
        
        return view('teacher.materials.show', compact('material'));
    }

    /**
     * Download material file
     */
    public function download(Material $material)
    {
        $teacher = Auth::user();
        
        // Check if teacher has access to this material (via assigned classes)
        $hasAccess = $material->syllabus->curriculum->course->classes()
                                ->whereHas('teachers', function($query) use ($teacher) {
                                    $query->where('users.id', $teacher->id);
                                })
                                ->where('status', 'active')
                                ->exists();
        
        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke materi ini.');
        }
        
        if (!$material->file_path || !Storage::exists($material->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }
        
        return Storage::download($material->file_path, $material->title . '.' . pathinfo($material->file_path, PATHINFO_EXTENSION));
    }
}