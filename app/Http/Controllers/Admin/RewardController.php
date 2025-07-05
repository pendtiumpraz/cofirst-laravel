<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\RewardRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RewardController extends Controller
{
    /**
     * Display a listing of rewards
     */
    public function index()
    {
        $rewards = Reward::withCount('redemptions')
            ->latest()
            ->paginate(20);
            
        return view('admin.rewards.index', compact('rewards'));
    }
    
    /**
     * Show the form for creating a new reward
     */
    public function create()
    {
        return view('admin.rewards.create');
    }
    
    /**
     * Store a newly created reward
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:physical,digital,privilege,discount',
            'points_cost' => 'required|integer|min:1',
            'quantity_available' => 'nullable|integer|min:1',
            'image' => 'nullable|image|max:2048',
            'metadata' => 'nullable|array',
            'is_active' => 'boolean',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
        ]);
        
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('rewards', 'public');
        }
        
        $validated['is_active'] = $request->has('is_active');
        $validated['quantity_redeemed'] = 0;
        
        Reward::create($validated);
        
        return redirect()->route('admin.rewards.index')
            ->with('success', 'Reward created successfully.');
    }
    
    /**
     * Display the specified reward
     */
    public function show(Reward $reward)
    {
        $reward->load(['redemptions.user', 'redemptions.processor']);
        
        return view('admin.rewards.show', compact('reward'));
    }
    
    /**
     * Show the form for editing the specified reward
     */
    public function edit(Reward $reward)
    {
        return view('admin.rewards.edit', compact('reward'));
    }
    
    /**
     * Update the specified reward
     */
    public function update(Request $request, Reward $reward)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:physical,digital,privilege,discount',
            'points_cost' => 'required|integer|min:1',
            'quantity_available' => 'nullable|integer|min:1',
            'image' => 'nullable|image|max:2048',
            'metadata' => 'nullable|array',
            'is_active' => 'boolean',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date|after:available_from',
        ]);
        
        if ($request->hasFile('image')) {
            // Delete old image
            if ($reward->image) {
                Storage::disk('public')->delete($reward->image);
            }
            $validated['image'] = $request->file('image')->store('rewards', 'public');
        }
        
        $validated['is_active'] = $request->has('is_active');
        
        $reward->update($validated);
        
        return redirect()->route('admin.rewards.index')
            ->with('success', 'Reward updated successfully.');
    }
    
    /**
     * Remove the specified reward
     */
    public function destroy(Reward $reward)
    {
        if ($reward->redemptions()->count() > 0) {
            return back()->with('error', 'Cannot delete reward that has redemptions.');
        }
        
        // Delete image
        if ($reward->image) {
            Storage::disk('public')->delete($reward->image);
        }
        
        $reward->delete();
        
        return redirect()->route('admin.rewards.index')
            ->with('success', 'Reward deleted successfully.');
    }
    
    /**
     * Toggle reward active status
     */
    public function toggleStatus(Reward $reward)
    {
        $reward->is_active = !$reward->is_active;
        $reward->save();
        
        return back()->with('success', 'Reward status updated.');
    }
    
    /**
     * Display redemptions
     */
    public function redemptions(Request $request)
    {
        $query = RewardRedemption::with(['user', 'reward', 'processor']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $redemptions = $query->latest()->paginate(20);
        
        return view('admin.rewards.redemptions', compact('redemptions'));
    }
    
    /**
     * Process redemption
     */
    public function processRedemption(RewardRedemption $redemption, Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,deliver,cancel',
            'notes' => 'nullable|string',
        ]);
        
        switch ($validated['action']) {
            case 'approve':
                $redemption->approve(auth()->id(), $validated['notes']);
                $message = 'Redemption approved successfully.';
                break;
                
            case 'deliver':
                $redemption->markDelivered(auth()->id(), $validated['notes']);
                $message = 'Redemption marked as delivered.';
                break;
                
            case 'cancel':
                $redemption->cancel(auth()->id(), $validated['notes']);
                $message = 'Redemption cancelled and points refunded.';
                break;
        }
        
        return back()->with('success', $message);
    }
}