<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.testimonials.index')->with([
            'message' => 'Testimonials management feature is coming soon!',
            'testimonials' => collect() // Empty collection for now
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.testimonials.create')->with([
            'message' => 'Testimonial creation feature is coming soon!'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return redirect()->route('admin.testimonials.index')
            ->with('info', 'Testimonial creation feature is coming soon!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('admin.testimonials.show')->with([
            'message' => 'Testimonial viewing feature is coming soon!',
            'testimonial' => null
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('admin.testimonials.edit')->with([
            'message' => 'Testimonial editing feature is coming soon!',
            'testimonial' => null
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return redirect()->route('admin.testimonials.index')
            ->with('info', 'Testimonial editing feature is coming soon!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return redirect()->route('admin.testimonials.index')
            ->with('info', 'Testimonial deletion feature is coming soon!');
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(string $id)
    {
        return redirect()->route('admin.testimonials.index')
            ->with('info', 'Testimonial status toggle feature is coming soon!');
    }
}
