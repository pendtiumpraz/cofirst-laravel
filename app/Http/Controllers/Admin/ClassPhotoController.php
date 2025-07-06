<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClassPhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.class-photos.index')->with([
            'message' => 'Class Photos management feature is coming soon!',
            'photos' => collect() // Empty collection for now
        ]);
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
        return view('admin.class-photos.show')->with([
            'message' => 'Class Photos viewing feature is coming soon!',
            'photo' => null
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('admin.class-photos.edit')->with([
            'message' => 'Class Photos editing feature is coming soon!',
            'photo' => null
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return redirect()->route('admin.class-photos.index')
            ->with('info', 'Class Photos editing feature is coming soon!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return redirect()->route('admin.class-photos.index')
            ->with('info', 'Class Photos deletion feature is coming soon!');
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
