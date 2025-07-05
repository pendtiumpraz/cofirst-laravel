<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HandoverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('teacher.handovers.index')->with([
            'message' => 'Student Handover feature is coming soon!',
            'handovers' => collect() // Empty collection for now
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('teacher.handovers.create')->with([
            'message' => 'Student Handover creation feature is coming soon!'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return redirect()->route('teacher.handovers.index')
            ->with('info', 'Student Handover creation feature is coming soon!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('teacher.handovers.show')->with([
            'message' => 'Student Handover viewing feature is coming soon!',
            'handover' => null
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('teacher.handovers.edit')->with([
            'message' => 'Student Handover editing feature is coming soon!',
            'handover' => null
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return redirect()->route('teacher.handovers.index')
            ->with('info', 'Student Handover editing feature is coming soon!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return redirect()->route('teacher.handovers.index')
            ->with('info', 'Student Handover deletion feature is coming soon!');
    }

    /**
     * Approve the specified handover.
     */
    public function approve(string $id)
    {
        return redirect()->route('teacher.handovers.index')
            ->with('info', 'Student Handover approval feature is coming soon!');
    }
}
