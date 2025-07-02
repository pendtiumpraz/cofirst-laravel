<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ClassName;
use App\Models\User;

// Landing page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Public routes
Route::get('/about', [LandingController::class, 'about'])->name('about');
Route::get('/courses', [LandingController::class, 'courses'])->name('courses');
Route::get('/contact', [LandingController::class, 'contact'])->name('contact');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard - Main dashboard for all users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin routes
    Route::middleware(['role:admin|superadmin'])->prefix('admin')->name('admin.')->group(function () {
        // User Management
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        
        // Course Management
        Route::resource('courses', CourseController::class);
        Route::post('courses/{course}/toggle-status', [CourseController::class, 'toggleStatus'])->name('courses.toggle-status');
        
        // Class Management
        Route::resource('classes', ClassController::class);
        Route::get('classes/{class}/students', [ClassController::class, 'students'])->name('classes.students');
        Route::post('classes/{class}/add-student', [ClassController::class, 'addStudent'])->name('classes.add-student');
        Route::delete('classes/{class}/remove-student/{student}', [ClassController::class, 'removeStudent'])->name('classes.remove-student');
        
        // Reports
        Route::get('reports', [ReportController::class, 'adminIndex'])->name('reports.index');
        
        // Enrollments
        Route::resource('enrollments', EnrollmentController::class);
        Route::post('enrollments/{enrollment}/toggle-status', [EnrollmentController::class, 'toggleStatus'])->name('enrollments.toggle-status');

        // Schedule Management
        Route::resource('schedules', \App\Http\Controllers\Admin\ScheduleController::class);

        // Role and Permission Management (SuperAdmin only)
        Route::middleware(['role:superadmin'])->group(function () {
            Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
            Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
        });
    });

    // Teacher routes
    Route::middleware(['role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('classes', [TeacherController::class, 'classes'])->name('classes');
        Route::get('classes/{class}', [TeacherController::class, 'classDetail'])->name('class-detail');
        Route::get('students', [TeacherController::class, 'students'])->name('students.index');
        
        // Report Management for Teachers
        Route::resource('reports', ReportController::class);
        Route::get('reports/class/{class}', [ReportController::class, 'byClass'])->name('reports.by-class');
        Route::get('reports/student/{student}', [ReportController::class, 'byStudent'])->name('reports.by-student');

        // Class Report (Berita Acara)
        Route::get('class-reports/create', [ClassReportController::class, 'create'])->name('class-reports.create');
        Route::post('class-reports', [ClassReportController::class, 'store'])->name('class-reports.store');
        Route::get('class-reports/get-schedules-and-students', [ClassReportController::class, 'getSchedulesAndStudents'])->name('class-reports.get-schedules-and-students');
    });

    // Parent routes
    Route::middleware(['role:parent'])->prefix('parent')->name('parent.')->group(function () {
        Route::get('dashboard', function () {
            $children = Auth::user()->children()->with('studentReports', 'enrollments')->get();
            return view('parent.dashboard', compact('children'));
        })->name('dashboard');
        
        Route::get('children', function () {
            $children = Auth::user()->children()->with('studentReports', 'enrollments')->get();
            return view('parent.children.index', compact('children'));
        })->name('children.index');
        
        Route::get('reports', function () {
            $children = Auth::user()->children()->with('studentReports', 'enrollments')->get();
            return view('parent.reports.index', compact('children'));
        })->name('reports.index');
        
        Route::get('child/{student}/reports', [StudentController::class, 'childReports'])->name('child-reports');
        Route::get('child/{student}/schedule', [StudentController::class, 'childSchedule'])->name('child-schedule');
        Route::get('child/{student}/payments', [StudentController::class, 'childPayments'])->name('child-payments');
    });

    // Student routes
    Route::middleware(['role:student'])->prefix('student')->name('student.')->group(function () {
        Route::get('classes', [StudentController::class, 'classes'])->name('classes');
        Route::get('schedules', [StudentController::class, 'schedule'])->name('schedules.index');
        Route::get('reports', [StudentController::class, 'reports'])->name('reports');
        Route::get('payments', [StudentController::class, 'payments'])->name('payments');
    });

    // Finance routes
    Route::middleware(['role:finance|admin|superadmin'])->prefix('finance')->name('finance.')->group(function () {
        Route::get('transactions', [FinanceController::class, 'index'])->name('transactions.list');
        Route::get('transactions/create', [FinanceController::class, 'create'])->name('transactions.create');
        Route::post('transactions', [FinanceController::class, 'store'])->name('transactions.store');
        Route::get('transactions/{transaction}/edit', [FinanceController::class, 'edit'])->name('transactions.edit');
        Route::put('transactions/{transaction}', [FinanceController::class, 'update'])->name('transactions.update');
        Route::delete('transactions/{transaction}', [FinanceController::class, 'destroy'])->name('transactions.destroy');
        Route::post('transactions/{transaction}/mark-paid', [FinanceController::class, 'markPaid'])->name('transactions.mark-paid');
        
        Route::get('reports', [FinanceController::class, 'reports'])->name('reports.index');
        Route::get('reports/daily', [FinanceController::class, 'dailyReport'])->name('reports.daily');
        Route::get('reports/export', [FinanceController::class, 'exportReport'])->name('reports.export');
    });

    // Debug routes
    // Test route for @extends layout
Route::get('/test-extends-layout', function () {
    return view('test-extends');
});

Route::get('/debug-info', function () {
        return view('debug-info');
    });
    
    Route::get('/simple-debug', function () {
        $user = auth()->user();
        return response()->json([
            'user' => $user->only(['id', 'name', 'email']),
            'roles' => $user->roles->pluck('name'),
            'has_admin' => $user->hasRole('admin'),
            'has_teacher' => $user->hasRole('teacher'),
            'has_student' => $user->hasRole('student'),
            'classes_count' => \App\Models\ClassName::count(),
            'enrollments_count' => \App\Models\Enrollment::count(),
        ]);
    });
    
    Route::get('/test-student-css', function () {
        $user = auth()->user();
        $enrollments = \App\Models\Enrollment::where('student_id', $user->id)
            ->with(['class.course', 'class.teacher'])
            ->where('status', 'active')
            ->get();
            
        return view('student.classes.index', compact('enrollments'));
    });
    
    Route::get('/test-teacher-css', function () {
        $user = auth()->user();
        $classes = \App\Models\ClassName::where('teacher_id', $user->id)
            ->with(['course', 'enrollments'])
            ->withCount('enrollments')
            ->where('status', 'active')
            ->get();
            
        $totalStudents = $classes->sum('enrollments_count');
        
        return view('teacher.classes.index', compact('classes', 'totalStudents'));
    });
    
    Route::get('/test-app-layout', function () {
        return view('test-app-layout');
    });
    
    Route::get('/test-student-simple', function () {
        return view('test-student-simple');
    });
    
    Route::get('/test-teacher-simple', function () {
        return view('test-teacher-simple');
    });
    
    Route::get('/test-student-simple-view', function () {
        $user = auth()->user();
        $enrollments = \App\Models\Enrollment::where('student_id', $user->id)
            ->with(['class.course', 'class.teacher'])
            ->where('status', 'active')
            ->get();
            
        return view('student.classes.simple-test', compact('enrollments'));
    });
});

// Test routes
Route::get('/test', function () {
    return view('test');
});

Route::get('/css-test', function () {
    return view('test-simple');
});

Route::get('/layout-test', function () {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Layout Test</title>
        ' . app(\Illuminate\Foundation\Vite::class)(['resources/css/app.css', 'resources/js/app.js']) . '
    </head>
    <body class="bg-gray-100">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-4xl font-bold text-blue-600 mb-6">Direct Layout Test</h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-red-500 text-white p-6 rounded-lg">
                    <h3 class="text-xl font-bold">Red Card</h3>
                    <p>Should be RED background</p>
                </div>
                <div class="bg-green-500 text-white p-6 rounded-lg">
                    <h3 class="text-xl font-bold">Green Card</h3>
                    <p>Should be GREEN background</p>
                </div>
                <div class="bg-blue-500 text-white p-6 rounded-lg">
                    <h3 class="text-xl font-bold">Blue Card</h3>
                    <p>Should be BLUE background</p>
                </div>
            </div>
        </div>
    </body>
    </html>';
});

Route::get('/component-test', function () {
    return view('test-component');
});

Route::get('/debug-classes', function () {
    $classes = \App\Models\ClassName::with(['course', 'teacher'])->withCount('enrollments')->get();
    return response()->json([
        'classes' => $classes,
        'count' => $classes->count()
    ]);
});

Route::get('/debug-teacher-classes', function () {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    $classes = \App\Models\ClassName::where('teacher_id', $user->id)
        ->with(['course', 'enrollments.student'])
        ->withCount('enrollments')
        ->where('status', 'active')
        ->get();
        
    return response()->json([
        'user' => $user->only(['id', 'name']),
        'roles' => $user->roles->pluck('name'),
        'classes' => $classes,
        'count' => $classes->count()
    ]);
});

Route::get('/debug-student-enrollments', function () {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    $enrollments = \App\Models\Enrollment::where('student_id', $user->id)
        ->with(['class.course', 'class.teacher'])
        ->where('status', 'active')
        ->get();
        
    return response()->json([
        'user' => $user->only(['id', 'name']),
        'roles' => $user->roles->pluck('name'),
        'enrollments' => $enrollments,
        'count' => $enrollments->count()
    ]);
});

Route::get('/layout-test', function () {
    return '<!DOCTYPE html>
<html>
<head>
    <title>Layout Test</title>
    <link rel="stylesheet" href="/build/assets/app--yI5Pw4a.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-blue-600 mb-6">Direct Layout Test</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-red-500 text-white p-6 rounded-lg">
                <h3 class="text-xl font-bold">Red Card</h3>
                <p>Should be RED background</p>
            </div>
            <div class="bg-green-500 text-white p-6 rounded-lg">
                <h3 class="text-xl font-bold">Green Card</h3>
                <p>Should be GREEN background</p>
            </div>
            <div class="bg-blue-500 text-white p-6 rounded-lg">
                <h3 class="text-xl font-bold">Blue Card</h3>
                <p>Should be BLUE background</p>
            </div>
        </div>
    </div>
</body>
</html>';
});

Route::get('/debug-user', function () {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }
    
    return response()->json([
        'user' => $user->only(['id', 'name', 'email']),
        'roles' => $user->roles->pluck('name'),
        'permissions' => $user->permissions->pluck('name'),
        'all_permissions' => $user->getAllPermissions()->pluck('name'),
        'has_admin_role' => $user->hasRole('admin'),
        'has_teacher_role' => $user->hasRole('teacher'),
        'has_student_role' => $user->hasRole('student'),
        'has_parent_role' => $user->hasRole('parent'),
    ]);
});

Route::get('/direct-css-test', function () {
    return '<!DOCTYPE html>
<html>
<head>
    <title>Direct CSS Test</title>
    <link rel="stylesheet" href="/build/assets/app--yI5Pw4a.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-blue-600 mb-6">Direct CSS Test</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-red-500 text-white p-6 rounded-lg">
                <h3 class="text-xl font-bold">Red Card</h3>
                <p>Should be RED background</p>
            </div>
            <div class="bg-green-500 text-white p-6 rounded-lg">
                <h3 class="text-xl font-bold">Green Card</h3>
                <p>Should be GREEN background</p>
            </div>
            <div class="bg-blue-500 text-white p-6 rounded-lg">
                <h3 class="text-xl font-bold">Blue Card</h3>
                <p>Should be BLUE background</p>
            </div>
        </div>
        <div class="mt-8 p-4 bg-yellow-200 rounded-lg">
            <p class="text-gray-800">If you see colors, CSS is loading correctly!</p>
        </div>
    </div>
</body>
</html>';
});

// Test route for @extends layout
Route::get('/test-extends-layout', function () {
    return view('test-extends');
});

Route::get('/debug-active-classes', function () {
    $activeClasses = ClassName::active()->get();
    return response()->json($activeClasses);
});

Route::get('/debug-active-students', function () {
    $activeStudents = User::role('student')->active()->get();
    return response()->json($activeStudents);
});

require __DIR__.'/auth.php';
