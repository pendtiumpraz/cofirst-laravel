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
use App\Http\Controllers\ClassReportController;
use App\Http\Controllers\Admin\CurriculumController;
use App\Http\Controllers\Admin\SyllabusController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Teacher\CurriculumController as TeacherCurriculumController;
use App\Http\Controllers\Student\MaterialController as StudentMaterialController;
use App\Http\Controllers\Parent\ProgressController;
use App\Http\Controllers\PhotoUploadController;
use App\Http\Controllers\ProjectGalleryController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ChatController;
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

// Certificate verification (public)
Route::get('/certificate/verify/{code}', [CertificateController::class, 'verify'])->name('certificate.verify');

// Authenticated routes
// Role switching routes
Route::middleware('auth')->group(function () {
    Route::get('/role-selection', [\App\Http\Controllers\RoleSwitchController::class, 'showRoleSelection'])->name('role.selection');
    Route::post('/role-selection', [\App\Http\Controllers\RoleSwitchController::class, 'setActiveRole'])->name('role.set');
    Route::post('/switch-role', [\App\Http\Controllers\RoleSwitchController::class, 'switchRole'])->name('role.switch');
});

Route::middleware('auth')->group(function () {
    // Dashboard - Main dashboard for all users
    Route::get('/dashboard', function () {
        $user = Auth::user();
        
        if ($user->hasRole('superadmin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('teacher')) {
            return redirect()->route('teacher.dashboard');
        } elseif ($user->hasRole('parent')) {
            return redirect()->route('parent.dashboard');
        } elseif ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        } elseif ($user->hasRole('finance')) {
            return redirect()->route('finance.dashboard');
        }
        
        // Default fallback
        return redirect()->route('login');
    })->name('dashboard');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Photo upload routes
    Route::post('/profile/photo', [PhotoUploadController::class, 'uploadProfilePhoto'])->name('profile.photo.upload');
    Route::delete('/profile/photo', [PhotoUploadController::class, 'deleteProfilePhoto'])->name('profile.photo.delete');
    
    // Project Gallery routes (accessible by students, teachers, and parents)
    Route::middleware(['role:student|teacher|parent|admin|superadmin'])->group(function () {
        Route::resource('project-gallery', ProjectGalleryController::class);
        Route::get('project-gallery/featured', [ProjectGalleryController::class, 'featured'])->name('project-gallery.featured');
    });

    // Admin routes
    Route::middleware(['role:admin|superadmin'])->prefix('admin')->name('admin.')->group(function () {
        // Admin Dashboard
        Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        
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
        
        // Curriculum Management
        Route::resource('curriculums', CurriculumController::class);
                    Route::post('curriculums/{curriculum}/toggle-status', [CurriculumController::class, 'toggleStatus'])->name('curriculums.toggle-status');
        
        // Syllabus Management
        Route::resource('syllabuses', SyllabusController::class);
                    Route::post('syllabuses/{syllabus}/toggle-status', [SyllabusController::class, 'toggleStatus'])->name('syllabuses.toggle-status');
        Route::get('syllabuses/by-curriculum/{curriculum}', [SyllabusController::class, 'getByCurriculum'])->name('syllabuses.by-curriculum');
        
        // Material Management
        Route::resource('materials', MaterialController::class);
                    Route::post('materials/{material}/toggle-status', [MaterialController::class, 'toggleStatus'])->name('materials.toggle-status');
        Route::get('materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
        Route::get('materials/by-syllabus/{syllabus}', [MaterialController::class, 'getBySyllabus'])->name('materials.by-syllabus');
        
        // Reports
        Route::get('reports', [ReportController::class, 'adminIndex'])->name('reports.index');
        
        // Enrollments
        Route::resource('enrollments', EnrollmentController::class);
        Route::post('enrollments/{enrollment}/toggle-status', [EnrollmentController::class, 'toggleStatus'])->name('enrollments.toggle-status');

        // Schedule Management
        Route::resource('schedules', \App\Http\Controllers\Admin\ScheduleController::class);
        
        // Photo Gallery Management
        Route::resource('class-photos', \App\Http\Controllers\Admin\ClassPhotoController::class);
        Route::post('class-photos/{photo}/toggle-status', [\App\Http\Controllers\Admin\ClassPhotoController::class, 'toggleStatus'])->name('class-photos.toggle-status');
        
        // Certificate Templates Management
        Route::resource('certificate-templates', \App\Http\Controllers\Admin\CertificateTemplateController::class);
        Route::post('certificate-templates/{template}/toggle-status', [\App\Http\Controllers\Admin\CertificateTemplateController::class, 'toggleStatus'])->name('certificate-templates.toggle-status');
        
        // Certificate Bulk Generation
        Route::get('certificates/bulk-generate', [\App\Http\Controllers\Admin\CertificateController::class, 'bulkGenerate'])->name('certificates.bulk-generate');
        Route::post('certificates/bulk-generate', [\App\Http\Controllers\Admin\CertificateController::class, 'processBulkGenerate'])->name('certificates.bulk-generate.process');
        
        // Testimonials Management
        Route::resource('testimonials', \App\Http\Controllers\Admin\TestimonialController::class);
                    Route::post('testimonials/{testimonial}/toggle-status', [\App\Http\Controllers\Admin\TestimonialController::class, 'toggleStatus'])->name('testimonials.toggle-status');
                    Route::post('testimonials/{testimonial}/toggle-featured', [\App\Http\Controllers\Admin\TestimonialController::class, 'toggleFeatured'])->name('testimonials.toggle-featured');
        
        // Gamification Management
                    Route::resource('badges', \App\Http\Controllers\Admin\BadgeController::class);
            Route::post('badges/{badge}/toggle-status', [\App\Http\Controllers\Admin\BadgeController::class, 'toggleStatus'])->name('badges.toggle-status');
        
        Route::get('rewards/redemptions', [\App\Http\Controllers\Admin\RewardController::class, 'redemptions'])->name('rewards.redemptions');
        Route::post('rewards/redemptions/{redemption}/process', [\App\Http\Controllers\Admin\RewardController::class, 'processRedemption'])->name('rewards.redemptions.process');
        Route::resource('rewards', \App\Http\Controllers\Admin\RewardController::class);
                    Route::post('rewards/{reward}/toggle-status', [\App\Http\Controllers\Admin\RewardController::class, 'toggleStatus'])->name('rewards.toggle-status');
        
        // Finance Reports for Admin
        Route::get('finance/dashboard', [\App\Http\Controllers\Admin\FinanceReportController::class, 'dashboard'])->name('finance.dashboard');
        Route::get('finance/reports', [\App\Http\Controllers\Admin\FinanceReportController::class, 'reports'])->name('finance.reports');
        Route::get('finance/export', [\App\Http\Controllers\Admin\FinanceReportController::class, 'export'])->name('finance.export');

        // Role and Permission Management (SuperAdmin only)
        Route::middleware(['role:superadmin'])->group(function () {
            Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
            Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
        });
    });

    // Teacher routes
    Route::middleware(['role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');
        Route::get('classes', [TeacherController::class, 'classes'])->name('classes');
        Route::get('classes/{class}', [TeacherController::class, 'classDetail'])->name('class-detail');
        Route::get('students', [TeacherController::class, 'students'])->name('students.index');
        
        // Curriculum (View Only)
        Route::get('curriculums', [TeacherCurriculumController::class, 'index'])->name('curriculums.index');
        Route::get('curriculums/{curriculum}', [TeacherCurriculumController::class, 'show'])->name('curriculums.show');
        Route::get('curriculums/{curriculum}/syllabuses', [TeacherCurriculumController::class, 'syllabuses'])->name('curriculums.syllabuses');
        Route::get('curriculums/{curriculum}/materials', [TeacherCurriculumController::class, 'materials'])->name('curriculums.materials');
        
        // Syllabuses (View Only)
        Route::get('syllabuses', [\App\Http\Controllers\Teacher\SyllabusController::class, 'index'])->name('syllabuses.index');
        Route::get('syllabuses/{syllabus}', [\App\Http\Controllers\Teacher\SyllabusController::class, 'show'])->name('syllabuses.show');
        
        // Materials (View Only)
        Route::get('materials', [\App\Http\Controllers\Teacher\MaterialController::class, 'index'])->name('materials.index');
        Route::get('materials/{material}', [\App\Http\Controllers\Teacher\MaterialController::class, 'show'])->name('materials.show');
        Route::get('materials/{material}/download', [\App\Http\Controllers\Teacher\MaterialController::class, 'download'])->name('materials.download');
        
        // Report Management for Teachers
        Route::resource('reports', ReportController::class);
        Route::get('reports/class/{class}', [ReportController::class, 'byClass'])->name('reports.by-class');
        Route::get('reports/student/{student}', [ReportController::class, 'byStudent'])->name('reports.by-student');
        
        // Teacher Handover Management
        Route::resource('handovers', \App\Http\Controllers\Teacher\HandoverController::class);
        Route::post('handovers/{handover}/approve', [\App\Http\Controllers\Teacher\HandoverController::class, 'approve'])->name('handovers.approve');
        
        // Teacher Class Photos
        Route::resource('class-photos', \App\Http\Controllers\Teacher\ClassPhotoController::class);
        Route::get('class-photos/upload/{class}', [\App\Http\Controllers\Teacher\ClassPhotoController::class, 'upload'])->name('class-photos.upload');
        
        // Teacher Certificate Generation
        Route::get('certificates/generate', [\App\Http\Controllers\Teacher\CertificateController::class, 'generate'])->name('certificates.generate');
        Route::post('certificates/generate', [\App\Http\Controllers\Teacher\CertificateController::class, 'processGenerate'])->name('certificates.process-generate');
        
        // Teacher Student Progress (Gamification)
        Route::get('student-progress', [\App\Http\Controllers\Teacher\StudentProgressController::class, 'index'])->name('student-progress.index');
        Route::get('student-progress/{student}', [\App\Http\Controllers\Teacher\StudentProgressController::class, 'show'])->name('student-progress.show');
        
        // Teacher Attendance (placeholder for future implementation)
        Route::get('attendance', [\App\Http\Controllers\Teacher\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/today', [\App\Http\Controllers\Teacher\AttendanceController::class, 'today'])->name('attendance.today');

        // Note: Class Reports are now handled in the global middleware group below
    });

    // Parent routes
    Route::middleware(['role:parent'])->prefix('parent')->name('parent.')->group(function () {
        Route::get('dashboard', function () {
            $children = Auth::user()->children()->with('studentReports', 'enrollments.className')->get();
            
            // Calculate stats for dashboard
            $stats = [
                'total_children' => $children->count(),
                'active_classes' => $children->sum(function ($child) {
                    return $child->enrollments->where('status', 'active')->count();
                }),
                'available_classes' => \App\Models\ClassName::where('is_active', true)
                    ->whereIn('status', ['planned', 'active'])
                    ->count(),
                'total_spent' => $children->sum(function ($child) {
                    return $child->enrollments->sum(function ($enrollment) {
                        return $enrollment->className->course->price ?? 0;
                    });
                })
            ];
            
            // Get purchased classes
            $purchasedClasses = collect();
            foreach ($children as $child) {
                foreach ($child->enrollments as $enrollment) {
                    if ($enrollment->status === 'active' && $enrollment->className) {
                        $purchasedClasses->push([
                            'child' => $child,
                            'class' => $enrollment->className,
                            'enrollment' => $enrollment
                        ]);
                    }
                }
            }
            
            // Get available classes
            $availableClasses = \App\Models\ClassName::where('is_active', true)
                ->whereIn('status', ['planned', 'active'])
                ->with('course', 'teacher')
                ->get();
            
            // Get schedules for current week (weekly recurring schedules)
            $childrenIds = Auth::user()->children()->get()->pluck('id');
            $schedules = \App\Models\Schedule::whereHas('enrollment', function ($query) use ($childrenIds) {
                $query->whereIn('student_id', $childrenIds);
            })
            ->where('is_active', true)
            ->with(['enrollment.student', 'className'])
            ->get();
            
            // Get today's schedules (based on current day of week)
            $todayDayOfWeek = now()->dayOfWeek === 0 ? 7 : now()->dayOfWeek; // Convert Sunday from 0 to 7
            $todaySchedules = \App\Models\Schedule::whereHas('enrollment', function ($query) use ($childrenIds) {
                $query->whereIn('student_id', $childrenIds);
            })
            ->where('is_active', true)
            ->where('day_of_week', $todayDayOfWeek)
            ->with(['enrollment.student', 'className'])
            ->get();
            
            return view('parent.dashboard', compact('children', 'stats', 'purchasedClasses', 'availableClasses', 'schedules', 'todaySchedules'));
        })->name('dashboard');
        
        Route::get('children', function () {
            $children = Auth::user()->children()->with('studentReports', 'enrollments')->get();
            return view('parent.children.index', compact('children'));
        })->name('children.index');
        
        Route::get('reports', function () {
            $children = Auth::user()->children()->with('studentReports', 'enrollments')->get();
            return view('parent.reports.index', compact('children'));
        })->name('reports.index');
        
        // Child Progress Tracking
        Route::get('progress', [ProgressController::class, 'index'])->name('progress.index');
        Route::get('progress/child/{child}', [ProgressController::class, 'child'])->name('progress.child');
        Route::get('progress/child/{child}/materials/{class}', [ProgressController::class, 'childMaterials'])->name('progress.child-materials');
        Route::get('progress/child/{child}/summary', [ProgressController::class, 'getChildProgressSummary'])->name('progress.child-summary');
        
        Route::get('child/{student}/reports', [StudentController::class, 'childReports'])->name('child-reports');
        Route::get('child/{student}/schedule', [StudentController::class, 'childSchedule'])->name('child-schedule');
        Route::get('child/{student}/payments', [StudentController::class, 'childPayments'])->name('child-payments');
        
        // Curriculum for Parents
        Route::get('curriculum', [\App\Http\Controllers\Parent\CurriculumController::class, 'index'])->name('curriculum.index');
        Route::get('curriculum/child/{child}/class/{class}', [\App\Http\Controllers\Parent\CurriculumController::class, 'showChildProgress'])->name('curriculum.child-progress');
        
        // Parent Class Photos
        Route::get('class-photos', [\App\Http\Controllers\Parent\ClassPhotoController::class, 'index'])->name('class-photos.index');
        Route::get('class-photos/{photo}', [\App\Http\Controllers\Parent\ClassPhotoController::class, 'show'])->name('class-photos.show');
        
        // Parent Testimonials
        Route::get('testimonials/create', [\App\Http\Controllers\Parent\TestimonialController::class, 'create'])->name('testimonials.create');
        Route::post('testimonials', [\App\Http\Controllers\Parent\TestimonialController::class, 'store'])->name('testimonials.store');
        Route::get('testimonials', [\App\Http\Controllers\Parent\TestimonialController::class, 'index'])->name('testimonials.index');
        
        // Parent Contact Admin
        Route::get('contact-admin', [\App\Http\Controllers\Parent\ContactController::class, 'index'])->name('contact-admin');
        Route::post('contact-admin', [\App\Http\Controllers\Parent\ContactController::class, 'send'])->name('contact-admin.send');
    });

    // Student routes
    Route::middleware(['role:student'])->prefix('student')->name('student.')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
        Route::get('classes', [StudentController::class, 'classes'])->name('classes');
        Route::get('courses', [StudentController::class, 'courses'])->name('courses.index');
        Route::get('schedules', [\App\Http\Controllers\Student\ScheduleController::class, 'index'])->name('schedules.index');
        Route::get('reports', [\App\Http\Controllers\Student\ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{report}', [\App\Http\Controllers\Student\ReportController::class, 'show'])->name('reports.show');
        Route::get('payments', [StudentController::class, 'payments'])->name('payments');
        
        // Materials and Progress
        Route::get('materials', [StudentMaterialController::class, 'index'])->name('materials.index');
        Route::get('materials/class/{class}', [StudentMaterialController::class, 'byClass'])->name('materials.by-class');
        Route::get('materials/{material}', [StudentMaterialController::class, 'show'])->name('materials.show');
        Route::get('materials/{material}/download', [StudentMaterialController::class, 'download'])->name('materials.download');
        Route::get('progress/class/{class}', [StudentMaterialController::class, 'progress'])->name('progress.class');
        
        // Curriculum for Students
        Route::get('curriculum', [\App\Http\Controllers\Student\CurriculumController::class, 'index'])->name('curriculum.index');
        Route::get('curriculum/class/{class}', [\App\Http\Controllers\Student\CurriculumController::class, 'show'])->name('curriculum.show');
    });

    // Class Reports - accessible by all authenticated users (admin, superadmin, teacher)
    Route::middleware(['role:admin|superadmin|teacher'])->group(function () {
        Route::resource('class-reports', ClassReportController::class);
        Route::get('class-reports/get-schedules-and-students', [ClassReportController::class, 'getSchedulesAndStudents'])->name('class-reports.get-schedules-and-students');
    });
    
    // Finance routes
    Route::middleware(['role:finance|admin|superadmin'])->prefix('finance')->name('finance.')->group(function () {
            Route::get('dashboard', [FinanceController::class, 'dashboard'])->name('dashboard');
    Route::get('chart-data', [FinanceController::class, 'getChartDataAjax'])->name('chart-data');
    Route::get('transactions', [FinanceController::class, 'index'])->name('transactions.index');
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

    // Certificate routes - accessible by all authenticated users with role-based filtering
    Route::middleware(['role:admin|superadmin|teacher|student|parent'])->group(function () {
        Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
        Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
    });

    // Certificate management - admin and teachers only
    Route::middleware(['role:admin|superadmin|teacher'])->group(function () {
        Route::get('certificates/create', [CertificateController::class, 'create'])->name('certificates.create');
        Route::post('certificates', [CertificateController::class, 'store'])->name('certificates.store');
        Route::get('certificates/bulk/create', [CertificateController::class, 'bulkCreate'])->name('certificates.bulk-create');
        Route::post('certificates/bulk', [CertificateController::class, 'bulkStore'])->name('certificates.bulk-store');
    });

    // Certificate invalidation - admin only
    Route::middleware(['role:admin|superadmin'])->group(function () {
        Route::patch('certificates/{certificate}/invalidate', [CertificateController::class, 'invalidate'])->name('certificates.invalidate');
    });

    // Chat routes - accessible by all authenticated users
    Route::middleware(['auth'])->prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::post('/start', [ChatController::class, 'startConversation'])->name('start');
        Route::get('/conversation/{conversation}', [ChatController::class, 'show'])->name('show');
        Route::post('/conversation/{conversation}/send', [ChatController::class, 'sendMessage'])->name('send');
        Route::post('/conversation/{conversation}/mark-read', [ChatController::class, 'markAsRead'])->name('mark-read');
        Route::put('/message/{message}', [ChatController::class, 'editMessage'])->name('message.edit');
        Route::delete('/message/{message}', [ChatController::class, 'deleteMessage'])->name('message.delete');
        Route::get('/search', [ChatController::class, 'search'])->name('search');
    });

    // Gamification routes - accessible by all authenticated users
    Route::middleware(['auth'])->prefix('gamification')->name('gamification.')->group(function () {
        Route::get('/', [\App\Http\Controllers\GamificationController::class, 'index'])->name('index');
        Route::get('/leaderboard', [\App\Http\Controllers\GamificationController::class, 'leaderboard'])->name('leaderboard');
        Route::get('/badges', [\App\Http\Controllers\GamificationController::class, 'badges'])->name('badges');
        Route::post('/badges/{badge}/toggle-featured', [\App\Http\Controllers\GamificationController::class, 'toggleBadgeFeatured'])->name('gamification.badges.toggle-featured');
        Route::get('/rewards', [\App\Http\Controllers\GamificationController::class, 'rewards'])->name('rewards');
        Route::post('/rewards/{reward}/redeem', [\App\Http\Controllers\GamificationController::class, 'redeemReward'])->name('rewards.redeem');
        Route::get('/redemptions', [\App\Http\Controllers\GamificationController::class, 'redemptions'])->name('redemptions');
        Route::get('/point-history', [\App\Http\Controllers\GamificationController::class, 'pointHistory'])->name('point-history');
    });

});

// All debug routes removed - issue resolved ✅

// Debug enrollment classes
Route::get('/debug-enrollment-classes', function () {
    $allClasses = \App\Models\ClassName::with(['course', 'teacher'])->get();
    $activeClasses = \App\Models\ClassName::where('is_active', true)->with(['course', 'teacher'])->get();
    $plannedActiveClasses = \App\Models\ClassName::where('is_active', true)
                          ->whereIn('status', ['planned', 'active'])
                          ->with(['course', 'teacher'])
                          ->get();
    
    return response()->json([
        'all_classes_count' => $allClasses->count(),
        'active_classes_count' => $activeClasses->count(),
        'planned_active_classes_count' => $plannedActiveClasses->count(),
        'all_classes' => $allClasses->map(function($class) {
            return [
                'id' => $class->id,
                'name' => $class->name,
                'status' => $class->status,
                'is_active' => $class->is_active,
                'course_name' => $class->course->name ?? 'No Course',
                'teacher_name' => $class->teacher->name ?? 'No Teacher'
            ];
        }),
        'filtered_classes' => $plannedActiveClasses->map(function($class) {
            return [
                'id' => $class->id,
                'name' => $class->name,
                'status' => $class->status,
                'is_active' => $class->is_active,
                'course_name' => $class->course->name ?? 'No Course',
                'teacher_name' => $class->teacher->name ?? 'No Teacher'
            ];
        })
    ]);
});

require __DIR__.'/auth.php';