<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClassPhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('teacher.class-photos.index')->with([
            'message' => 'Class Photos feature is coming soon!',
            'photos' => collect()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('teacher.class-photos.create')->with([
            'message' => 'Photo upload feature is coming soon!'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return redirect()->route('teacher.class-photos.index')
            ->with('info', 'Photo upload feature is coming soon!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('teacher.class-photos.show')->with([
            'message' => 'Photo viewing feature is coming soon!',
            'photo' => null
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('teacher.class-photos.edit')->with([
            'message' => 'Photo editing feature is coming soon!',
            'photo' => null
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return redirect()->route('teacher.class-photos.index')
            ->with('info', 'Photo editing feature is coming soon!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return redirect()->route('teacher.class-photos.index')
            ->with('info', 'Photo deletion feature is coming soon!');
    }

    /**
     * Show upload form for specific class.
     */
    public function upload(string $classId)
    {
        return view('teacher.class-photos.upload')->with([
            'message' => 'Class photo upload feature is coming soon!',
            'class' => null
        ]);
    }
}
