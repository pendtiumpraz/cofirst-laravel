<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestimonialController extends Controller
{
    /**
     * Show the form for creating/editing testimonial
     */
    public function create()
    {
        $user = Auth::user();
        
        // Check if user already has a testimonial
        $testimonial = Testimonial::where('user_id', $user->id)->first();
        
        return view('parent.testimonials.create', compact('testimonial'));
    }

    /**
     * Store or update the testimonial
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:50|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'child_name' => 'required|string|max:255',
            'child_class' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        
        // Check if testimonial already exists
        $testimonial = Testimonial::where('user_id', $user->id)->first();
        
        if ($testimonial) {
            // Update existing testimonial
            $testimonial->update([
                'title' => $request->title,
                'content' => $request->content,
                'rating' => $request->rating,
                'child_name' => $request->child_name,
                'child_class' => $request->child_class,
                'is_active' => false, // Reset approval status
            ]);
            
            $message = 'Your testimonial has been updated successfully and is pending admin approval.';
        } else {
            // Create new testimonial
            Testimonial::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'content' => $request->content,
                'rating' => $request->rating,
                'child_name' => $request->child_name,
                'child_class' => $request->child_class,
                'is_active' => false, // Pending admin approval
                'is_featured' => false,
            ]);
            
            $message = 'Your testimonial has been submitted successfully and is pending admin approval.';
        }

        return redirect()->route('parent.testimonials.create')->with('success', $message);
    }
}
