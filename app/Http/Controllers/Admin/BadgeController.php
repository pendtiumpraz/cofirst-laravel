<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BadgeController extends Controller
{
    /**
     * Display a listing of badges
     */
    public function index(Request $request)
    {
        $query = Badge::withCount('users');
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }
        
        // Filter by level
        if ($request->has('level') && $request->level) {
            $query->where('level', $request->level);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Sorting
        $sortField = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        
        if (in_array($sortField, ['name', 'category', 'level', 'points_required', 'created_at', 'sort_order'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            // Default sorting
            $query->orderBy('category')
                  ->orderBy('level')
                  ->orderBy('sort_order');
        }
        
        $badges = $query->paginate($request->get('per_page', 20))
                       ->appends($request->query());
            
        return view('admin.badges.index', compact('badges'));
    }
    
    /**
     * Show the form for creating a new badge
     */
    public function create()
    {
        return view('admin.badges.create');
    }
    
    /**
     * Store a newly created badge
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:255',
            'category' => 'required|in:academic,social,attendance,special',
            'level' => 'required|in:bronze,silver,gold,platinum',
            'points_required' => 'required|integer|min:0',
            'criteria' => 'required|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);
        
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        
        Badge::create($validated);
        
        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge created successfully.');
    }
    
    /**
     * Display the specified badge
     */
    public function show(Badge $badge)
    {
        $badge->load('users');
        
        return view('admin.badges.show', compact('badge'));
    }
    
    /**
     * Show the form for editing the specified badge
     */
    public function edit(Badge $badge)
    {
        return view('admin.badges.edit', compact('badge'));
    }
    
    /**
     * Update the specified badge
     */
    public function update(Request $request, Badge $badge)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:255',
            'category' => 'required|in:academic,social,attendance,special',
            'level' => 'required|in:bronze,silver,gold,platinum',
            'points_required' => 'required|integer|min:0',
            'criteria' => 'required|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);
        
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active');
        
        $badge->update($validated);
        
        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge updated successfully.');
    }
    
    /**
     * Remove the specified badge
     */
    public function destroy(Badge $badge)
    {
        if ($badge->users()->count() > 0) {
            return back()->with('error', 'Cannot delete badge that has been earned by users.');
        }
        
        $badge->delete();
        
        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge deleted successfully.');
    }
    
    /**
     * Toggle badge active status
     */
    public function toggleStatus(Badge $badge)
    {
        $badge->is_active = !$badge->is_active;
        $badge->save();
        
        return back()->with('success', 'Badge status updated.');
    }
}