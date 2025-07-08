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
        // Get available classes for registration
        $classes = \App\Models\ClassName::with(['course', 'teachers'])
            ->where('is_active', true)
            ->whereIn('status', ['planned'])
            ->whereDate('start_date', '>=', now())
            ->whereRaw('(SELECT COUNT(*) FROM enrollments WHERE class_id = class_names.id AND status IN ("active", "pending")) < max_students')
            ->orderBy('start_date')
            ->get();
            
        return view('auth.register', compact('classes'));
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
            'class_id' => ['required', 'exists:class_names,id'],
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
            
            // Get the selected class
            $class = ClassName::with('course')->findOrFail($request->class_id);
            
            // Verify class is still available
            $currentEnrollments = Enrollment::where('class_id', $class->id)
                ->whereIn('status', ['active', 'pending'])
                ->count();
                
            if ($currentEnrollments >= $class->max_students) {
                throw new \Exception('Class is full. Please select another class.');
            }
            
            // Create enrollment with selected class
            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'course_id' => $class->course_id,
                'class_id' => $class->id,
                'enrollment_date' => now(),
                'status' => 'pending', // Pending payment and admin approval
                'payment_status' => 'unpaid',
            ]);
            
            DB::commit();
            
            // Send registration notification to admin
            // TODO: Implement notification
            
            event(new Registered($student));
            event(new Registered($parent));
            
            // Redirect to success page with registration info
            return redirect()->route('register.success')->with([
                'student_email' => $studentUsername,
                'parent_email' => $parentUsername,
                'course_name' => $class->course->name,
                'class_name' => $class->name,
                'class_start_date' => $class->start_date->format('d M Y'),
                'teacher_name' => $class->teachers->first()->name ?? 'TBA',
                'message' => 'Registration successful! Your accounts have been created but are inactive. Please wait for admin approval after payment.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Registration failed. Please try again.')->withInput();
        }
    }
}
