<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\UserController;
// use App\Http\Controllers\Api\CourseController;
// use App\Http\Controllers\Api\ClassController;
// use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\ScheduleDataController;
// use App\Http\Controllers\Api\EnrollmentController;
// use App\Http\Controllers\Api\ReportController;
// use App\Http\Controllers\Api\FinancialController;
// use App\Http\Controllers\Api\AttendanceController;
// use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes
// Route::prefix('v1')->group(function () {
//     // Authentication routes
//     Route::post('/auth/login', [AuthController::class, 'login']);
//     Route::post('/auth/register', [AuthController::class, 'register']);
//     Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
//     Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
//     
//     // Public course listing
//     Route::get('/courses', [CourseController::class, 'publicIndex']);
//     Route::get('/courses/{course:slug}', [CourseController::class, 'publicShow']);
// });

// Protected API routes
// Route::prefix('v1')->middleware('auth:web')->group(function () {
//     // Authentication
//     Route::post('/auth/logout', [AuthController::class, 'logout']);
//     Route::post('/auth/refresh', [AuthController::class, 'refresh']);
//     Route::get('/auth/me', [AuthController::class, 'me']);
//     Route::put('/auth/update-profile', [AuthController::class, 'updateProfile']);
//     Route::put('/auth/change-password', [AuthController::class, 'changePassword']);
//     
//     // Dashboard data
//     Route::get('/dashboard', [DashboardController::class, 'index']);
//     Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
//     
//     // User management (Admin & SuperAdmin only)
//     Route::middleware(['role:admin|superadmin'])->group(function () {
//         Route::apiResource('users', UserController::class);
//         Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
//         Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole']);
//         Route::get('/users/{user}/activities', [UserController::class, 'activities']);
//     });
//     
//     // Course management
//     Route::apiResource('courses', CourseController::class);
//     Route::middleware(['role:admin|superadmin'])->group(function () {
//         Route::post('/courses/{course}/toggle-status', [CourseController::class, 'toggleStatus']);
//         Route::post('/courses/{course}/upload-thumbnail', [CourseController::class, 'uploadThumbnail']);
//     });
//     
//     // Class management
//     Route::apiResource('classes', ClassController::class);
//     Route::get('/classes/{class}/students', [ClassController::class, 'students']);
//     Route::middleware(['role:admin|superadmin|teacher'])->group(function () {
//         Route::post('/classes/{class}/add-student', [ClassController::class, 'addStudent']);
//         Route::delete('/classes/{class}/remove-student/{student}', [ClassController::class, 'removeStudent']);
//         Route::put('/classes/{class}/update-status', [ClassController::class, 'updateStatus']);
//     });
//     
//     // Schedule management
//     Route::apiResource('schedules', ScheduleController::class);
//     Route::get('/schedules/calendar/{month}/{year}', [ScheduleController::class, 'calendar']);
//     Route::get('/schedules/user/{user}', [ScheduleController::class, 'userSchedules']);
//     Route::get('/schedules/class/{class}', [ScheduleController::class, 'classSchedules']);
//     Route::get('/schedules/today', [ScheduleController::class, 'todaySchedules']);

//     // Schedule Data for Admin Panel
//     Route::get('/schedule-data/teachers/{class_id}', [ScheduleDataController::class, 'getTeachersByClass']);
//     Route::get('/schedule-data/enrollments/{class_id}', [ScheduleDataController::class, 'getEnrollmentsByClass']);
//     
//     // Enrollment management
//     Route::apiResource('enrollments', EnrollmentController::class);
//     Route::middleware(['role:admin|superadmin'])->group(function () {
//         Route::post('/enrollments/{enrollment}/toggle-status', [EnrollmentController::class, 'toggleStatus']);
//         Route::get('/enrollments/student/{student}', [EnrollmentController::class, 'studentEnrollments']);
//         Route::get('/enrollments/class/{class}', [EnrollmentController::class, 'classEnrollments']);
//     });
//     
//     // Report management
//     Route::apiResource('reports', ReportController::class);
//     Route::get('/reports/student/{student}', [ReportController::class, 'studentReports']);
//     Route::get('/reports/class/{class}', [ReportController::class, 'classReports']);
//     Route::get('/reports/teacher/{teacher}', [ReportController::class, 'teacherReports']);
//     Route::middleware(['role:teacher|admin|superadmin'])->group(function () {
//         Route::post('/reports/{report}/submit', [ReportController::class, 'submit']);
//     });
//     
//     // Financial management
//     Route::middleware(['role:finance|admin|superadmin'])->group(function () {
//         Route::apiResource('transactions', FinancialController::class);
//         Route::post('/transactions/{transaction}/mark-paid', [FinancialController::class, 'markPaid']);
//         Route::post('/transactions/{transaction}/cancel', [FinancialController::class, 'cancel']);
//         Route::get('/transactions/student/{student}', [FinancialController::class, 'studentTransactions']);
//         Route::get('/transactions/daily-report', [FinancialController::class, 'dailyReport']);
//         Route::get('/transactions/monthly-report/{month}/{year}', [FinancialController::class, 'monthlyReport']);
//         Route::post('/transactions/export', [FinancialController::class, 'export']);
//     });
//     
//     // Attendance management
//     Route::middleware(['role:teacher|admin|superadmin'])->group(function () {
//         Route::apiResource('attendance', AttendanceController::class);
//         Route::post('/attendance/batch', [AttendanceController::class, 'batchStore']);
//         Route::get('/attendance/schedule/{schedule}', [AttendanceController::class, 'scheduleAttendance']);
//         Route::get('/attendance/student/{student}', [AttendanceController::class, 'studentAttendance']);
//         Route::get('/attendance/class/{class}', [AttendanceController::class, 'classAttendance']);
//     });
//     
//     // Teacher specific routes
//     Route::middleware(['role:teacher'])->prefix('teacher')->group(function () {
//         Route::get('/my-classes', [ClassController::class, 'myClasses']);
//         Route::get('/my-students', [UserController::class, 'myStudents']);
//         Route::get('/my-schedule', [ScheduleController::class, 'mySchedule']);
//         Route::post('/handover/request', [ClassController::class, 'requestHandover']);
//     });
//     
//     // Student specific routes
//     Route::middleware(['role:student'])->prefix('student')->group(function () {
//         Route::get('/my-classes', [EnrollmentController::class, 'myClasses']);
//         Route::get('/my-schedule', [ScheduleController::class, 'mySchedule']);
//         Route::get('/my-reports', [ReportController::class, 'myReports']);
//         Route::get('/my-payments', [FinancialController::class, 'myPayments']);
//         Route::get('/available-courses', [CourseController::class, 'availableCourses']);
//     });
//     
//     // Parent specific routes
//     Route::middleware(['role:parent'])->prefix('parent')->group(function () {
//         Route::get('/my-children', [UserController::class, 'myChildren']);
//         Route::get('/child/{child}/reports', [ReportController::class, 'childReports']);
//         Route::get('/child/{child}/schedule', [ScheduleController::class, 'childSchedule']);
//         Route::get('/child/{child}/payments', [FinancialController::class, 'childPayments']);
//         Route::get('/child/{child}/attendance', [AttendanceController::class, 'childAttendance']);
//     });
    
//     // Analytics routes (SuperAdmin only)
//     Route::middleware(['role:superadmin'])->prefix('analytics')->group(function () {
//         Route::get('/overview', [DashboardController::class, 'analyticsOverview']);
//         Route::get('/revenue', [DashboardController::class, 'revenueAnalytics']);
//         Route::get('/users', [DashboardController::class, 'userAnalytics']);
//         Route::get('/courses', [DashboardController::class, 'courseAnalytics']);
//         Route::get('/performance', [DashboardController::class, 'performanceAnalytics']);
//     });
// });

// API documentation route
Route::get('/v1/docs', function () {
    return response()->json([
        'message' => 'Coding First LMS API Documentation',
        'version' => '1.0.0',
        'endpoints' => [
            'authentication' => '/api/v1/auth/*',
            'users' => '/api/v1/users/*',
            'courses' => '/api/v1/courses/*',
            'classes' => '/api/v1/classes/*',
            'schedules' => '/api/v1/schedules/*',
            'enrollments' => '/api/v1/enrollments/*',
            'reports' => '/api/v1/reports/*',
            'transactions' => '/api/v1/transactions/*',
            'attendance' => '/api/v1/attendance/*',
            'schedule-data' => '/api/v1/schedule-data/*',
        ]
    ]);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:web');

// Schedule Data API for dynamic forms
Route::middleware('auth:web')->group(function () {
    Route::get('/schedule-data/teachers/{class_id}', [ScheduleDataController::class, 'getTeachersByClass']);
    Route::get('/schedule-data/students/{class_id}', [ScheduleDataController::class, 'getStudentsByClass']);
});
// API Routes for future mobile app or external integrations
Route::prefix('v1')->middleware('auth:web')->group(function () {
    // User routes
    Route::get('/users', function () {
        return response()->json(['message' => 'Users API endpoint']);
    });
    
    // Course routes
    Route::get('/courses', function () {
        return response()->json(['message' => 'Courses API endpoint']);
    });
    
    // Class routes
    Route::get('/classes', function () {
        return response()->json(['message' => 'Classes API endpoint']);
    });
    
    // Report routes
    Route::get('/reports', function () {
        return response()->json(['message' => 'Reports API endpoint']);
    });
    
    // Transaction routes
    Route::get('/transactions', function () {
        return response()->json(['message' => 'Transactions API endpoint']);
    });
});
