<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassName;
use App\Models\Enrollment;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // Get available courses for registration
        $courses = \App\Models\Course::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('auth.register', compact('courses'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'course_id' => ['required', 'exists:courses,id'],
        ]);

        DB::beginTransaction();
        
        try {
            // Generate student username from name
            $studentUsername = Str::slug($request->name) . '@coding1st.com';
            $counter = 1;
            while (User::where('email', $studentUsername)->exists()) {
                $studentUsername = Str::slug($request->name) . $counter . '@coding1st.com';
                $counter++;
            }
            
            // Create student account
            $student = User::create([
                'name' => $request->name,
                'email' => $studentUsername,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'is_active' => false, // Inactive until payment
            ]);
            
            // Assign student role
            $student->assignRole('student');
            
            // Generate parent username
            $parentUsername = 'parent-' . Str::slug($request->name) . '@coding1st.com';
            $counter = 1;
            while (User::where('email', $parentUsername)->exists()) {
                $parentUsername = 'parent-' . Str::slug($request->name) . $counter . '@coding1st.com';
                $counter++;
            }
            
            // Create parent account
            $parent = User::create([
                'name' => $request->parent_name,
                'email' => $parentUsername,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'is_active' => false, // Inactive until payment
            ]);
            
            // Assign parent role
            $parent->assignRole('parent');
            
            // Link parent to student
            DB::table('parent_student')->insert([
                'parent_id' => $parent->id,
                'student_id' => $student->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Find the next available class for the selected course
            $class = ClassName::where('course_id', $request->course_id)
                ->where('is_active', true)
                ->where('status', 'planned')
                ->whereDate('start_date', '>=', now())
                ->whereRaw('(SELECT COUNT(*) FROM enrollments WHERE class_id = class_names.id AND status = "active") < max_students')
                ->orderBy('start_date')
                ->first();
            
            if (!$class) {
                // If no class is available, create enrollment without class
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $request->course_id,
                    'enrollment_date' => now(),
                    'status' => 'pending', // Pending payment
                    'payment_status' => 'unpaid',
                ]);
            } else {
                // Create enrollment with class
                $enrollment = Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $request->course_id,
                    'class_id' => $class->id,
                    'enrollment_date' => now(),
                    'status' => 'pending', // Pending payment
                    'payment_status' => 'unpaid',
                ]);
            }
            
            DB::commit();
            
            // Send registration notification to admin
            // TODO: Implement notification
            
            event(new Registered($student));
            event(new Registered($parent));
            
            // Redirect to success page with registration info
            return redirect()->route('register.success')->with([
                'student_email' => $studentUsername,
                'parent_email' => $parentUsername,
                'course_name' => \App\Models\Course::find($request->course_id)->name,
                'class_name' => $class ? $class->name : 'Will be assigned after payment',
                'message' => 'Registration successful! Your accounts have been created but are inactive. Please wait for admin approval after payment.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Registration failed. Please try again.')->withInput();
        }
    }
}
