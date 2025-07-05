<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Show the form for creating a testimonial.
     */
    public function create()
    {
        return view('parent.testimonials.create')->with([
            'message' => 'Testimonial submission feature is coming soon!'
        ]);
    }

    /**
     * Store a testimonial.
     */
    public function store(Request $request)
    {
        return redirect()->route('parent.testimonials.create')
            ->with('info', 'Testimonial submission feature is coming soon!');
    }

    /**
     * Display submitted testimonials.
     */
    public function index()
    {
        return view('parent.testimonials.index')->with([
            'message' => 'Testimonial viewing feature is coming soon!',
            'testimonials' => collect()
        ]);
    }
}
