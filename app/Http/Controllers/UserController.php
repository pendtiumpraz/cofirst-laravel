<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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
        
        // Get students who don't have parents yet for parent-student relationship
        $availableStudents = User::role('student')
            ->whereDoesntHave('parents')
            ->orderBy('name')
            ->get();
            
        return view('admin.users.create', compact('roles', 'availableStudents'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
        ];

        // Add validation for student_ids if role is parent
        if ($request->role === 'parent') {
            $validationRules['student_ids'] = 'required|array|min:1';
            $validationRules['student_ids.*'] = 'exists:users,id';
        }

        $request->validate($validationRules);

        DB::beginTransaction();
        
        try {
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

            // Handle parent-student relationship if role is parent
            if ($request->role === 'parent' && $request->has('student_ids')) {
                foreach ($request->student_ids as $studentId) {
                    // Verify student doesn't already have this parent
                    $existingRelation = DB::table('parent_student')
                        ->where('parent_id', $user->id)
                        ->where('student_id', $studentId)
                        ->exists();
                    
                    if (!$existingRelation) {
                        DB::table('parent_student')->insert([
                            'parent_id' => $user->id,
                            'student_id' => $studentId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            
            $message = 'User created successfully.';
            if ($request->role === 'parent' && $request->has('student_ids')) {
                $studentCount = count($request->student_ids);
                $message = "Parent created successfully and linked to {$studentCount} student(s).";
            }
            
            return redirect()->route('admin.users.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to create user. Please try again.')->withInput();
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('roles', 'profile');
        
        // Load children if user is a parent
        if ($user->hasRole('parent')) {
            $user->load('children');
        }
        
        // Load parents if user is a student
        if ($user->hasRole('student')) {
            $user->load('parents');
        }
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the user.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        
        // Get students who don't have parents yet, plus current children if user is parent
        $availableStudents = User::role('student')
            ->where(function($query) use ($user) {
                $query->whereDoesntHave('parents');
                
                // Include current children if user is parent
                if ($user->hasRole('parent')) {
                    $query->orWhereHas('parents', function($parentQuery) use ($user) {
                        $parentQuery->where('parent_id', $user->id);
                    });
                }
            })
            ->orderBy('name')
            ->get();
            
        // Get current children IDs if user is parent
        $currentChildrenIds = $user->hasRole('parent') ? $user->children->pluck('id')->toArray() : [];
        
        return view('admin.users.edit', compact('user', 'roles', 'availableStudents', 'currentChildrenIds'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
        ];

        // Add validation for student_ids if role is parent
        if ($request->role === 'parent') {
            $validationRules['student_ids'] = 'nullable|array';
            $validationRules['student_ids.*'] = 'exists:users,id';
        }

        $request->validate($validationRules);

        DB::beginTransaction();
        
        try {
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

            // Handle parent-student relationship updates
            if ($request->role === 'parent') {
                // Remove existing relationships for this parent
                DB::table('parent_student')->where('parent_id', $user->id)->delete();
                
                // Add new relationships
                if ($request->has('student_ids') && !empty($request->student_ids)) {
                    foreach ($request->student_ids as $studentId) {
                        DB::table('parent_student')->insert([
                            'parent_id' => $user->id,
                            'student_id' => $studentId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            
            $message = 'User updated successfully.';
            if ($request->role === 'parent') {
                $studentCount = $request->has('student_ids') ? count($request->student_ids) : 0;
                $message = "Parent updated successfully and linked to {$studentCount} student(s).";
            }
            
            return redirect()->route('admin.users.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to update user. Please try again.')->withInput();
        }
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
