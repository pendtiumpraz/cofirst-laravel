<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::with('roles')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => $request->boolean('is_active', true),
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = 'profile-photos/' . uniqid() . '-' . time() . '.' . $photo->getClientOriginalExtension();
            
            // Store the file directly
            $path = $photo->storeAs('profile-photos', basename($filename), 'public');
            $userData['profile_photo_path'] = $path;
        }

        $user = User::create($userData);
        $user->assignRole($request->role);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('roles', 'profile');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the user.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => $request->boolean('is_active', true),
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $photo = $request->file('photo');
            $filename = 'profile-photos/' . $user->id . '-' . time() . '.' . $photo->getClientOriginalExtension();
            
            // Store the file directly
            $path = $photo->storeAs('profile-photos', basename($filename), 'public');
            $userData['profile_photo_path'] = $path;
        }

        $user->update($userData);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting current user
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete yourself.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.users.index')->with('success', "User {$status} successfully.");
    }
}
