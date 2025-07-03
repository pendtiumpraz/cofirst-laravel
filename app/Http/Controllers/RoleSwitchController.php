<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class RoleSwitchController extends Controller
{
    /**
     * Show role selection page for users with multiple roles
     */
    public function showRoleSelection()
    {
        $user = Auth::user();
        
        // Check if user has both parent and student roles
        if ($user->hasRole('parent') && $user->hasRole('student')) {
            return view('auth.role-selection');
        }
        
        // If user doesn't have multiple roles, redirect to dashboard
        return redirect()->route('dashboard');
    }
    
    /**
     * Set the active role for the session
     */
    public function setActiveRole(Request $request)
    {
        $request->validate([
            'role' => 'required|in:parent,student'
        ]);
        
        $user = Auth::user();
        $selectedRole = $request->input('role');
        
        // Verify user has the selected role
        if (!$user->hasRole($selectedRole)) {
            return back()->withErrors(['role' => 'You do not have permission for this role.']);
        }
        
        // Store active role in session
        Session::put('active_role', $selectedRole);
        
        return redirect()->route('dashboard');
    }
    
    /**
     * Switch role during session
     */
    public function switchRole(Request $request)
    {
        $request->validate([
            'role' => 'required|in:parent,student'
        ]);
        
        $user = Auth::user();
        $selectedRole = $request->input('role');
        
        // Verify user has the selected role
        if (!$user->hasRole($selectedRole)) {
            return response()->json(['error' => 'You do not have permission for this role.'], 403);
        }
        
        // Store active role in session
        Session::put('active_role', $selectedRole);
        
        return response()->json(['success' => true, 'role' => $selectedRole]);
    }
    
    /**
     * Get current active role
     */
    public function getCurrentRole()
    {
        $activeRole = Session::get('active_role');
        $user = Auth::user();
        
        // If no active role set, determine default
        if (!$activeRole) {
            if ($user->hasRole('parent') && $user->hasRole('student')) {
                $activeRole = 'student'; // Default to student
            } elseif ($user->hasRole('parent')) {
                $activeRole = 'parent';
            } elseif ($user->hasRole('student')) {
                $activeRole = 'student';
            }
            
            if ($activeRole) {
                Session::put('active_role', $activeRole);
            }
        }
        
        return $activeRole;
    }
}