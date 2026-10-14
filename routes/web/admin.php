<?php

/*
|--------------------------------------------------------------------------
| Admin routes (admin, superadmin)
|--------------------------------------------------------------------------
|
| Loaded inside the "auth" middleware group from routes/web.php.
| Settings live in routes/web/settings.php and the privacy/PDP back-office
| in routes/web/privacy-admin.php.
|
*/

use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\CurriculumController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\SyllabusController;
use App\Http\Controllers\Api\ScheduleDataController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['role:admin|superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('teachers/performance', [Admin\DashboardController::class, 'teachers'])->name('teachers.ranking');

    // Users (static segments before the resource wildcard)
    Route::get('users/search', [UserController::class, 'search'])->name('users.search');
    Route::resource('users', UserController::class)->withTrashed(['show']);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{user}/restore', [UserController::class, 'restore'])->withTrashed()->name('users.restore');
    Route::post('users/{user}/password-link', [UserController::class, 'sendPasswordLink'])->name('users.password-link');
    Route::post('users/{user}/family', [UserController::class, 'attachFamily'])->name('users.family.attach');
    Route::delete('users/{user}/family/{related}', [UserController::class, 'detachFamily'])->name('users.family.detach');

    // Courses
    Route::resource('courses', CourseController::class)->withTrashed(['show']);
    Route::post('courses/{course}/toggle-status', [CourseController::class, 'toggleStatus'])->name('courses.toggle-status');
    Route::post('courses/{course}/restore', [CourseController::class, 'restore'])->withTrashed()->name('courses.restore');
    Route::get('courses/{course}/curriculum', [ClassController::class, 'getCurriculumByCourse'])->name('courses.curriculum');

    // Classes
    Route::resource('classes', ClassController::class)->withTrashed(['show']);
    Route::post('classes/{class}/restore', [ClassController::class, 'restore'])->withTrashed()->name('classes.restore');
    Route::get('classes/{class}/students', [ClassController::class, 'students'])->name('classes.students');
    Route::post('classes/{class}/add-student', [ClassController::class, 'addStudent'])->name('classes.add-student');
    Route::delete('classes/{class}/remove-student/{student}', [ClassController::class, 'removeStudent'])->name('classes.remove-student');
    Route::patch('classes/{class}/enrollments/{enrollment}/status', [ClassController::class, 'updateStudentStatus'])->name('classes.student-status');

    // Curriculum, syllabus, materials
    Route::resource('curriculums', CurriculumController::class);
    Route::post('curriculums/{curriculum}/toggle-status', [CurriculumController::class, 'toggleStatus'])->name('curriculums.toggle-status');

    Route::get('syllabuses/by-curriculum/{curriculum}', [SyllabusController::class, 'getByCurriculum'])->name('syllabuses.by-curriculum');
    Route::resource('syllabuses', SyllabusController::class);
    Route::post('syllabuses/{syllabus}/toggle-status', [SyllabusController::class, 'toggleStatus'])->name('syllabuses.toggle-status');

    Route::get('materials/by-syllabus/{syllabus}', [MaterialController::class, 'getBySyllabus'])->name('materials.by-syllabus');
    Route::resource('materials', MaterialController::class);
    Route::post('materials/{material}/toggle-status', [MaterialController::class, 'toggleStatus'])->name('materials.toggle-status');
    Route::get('materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
    Route::post('materials/{material}/move', [MaterialController::class, 'move'])->name('materials.move');

    // Reports
    Route::get('reports', [ReportController::class, 'adminIndex'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'adminExport'])->name('reports.export');
    Route::get('reports/{report}', [ReportController::class, 'adminShow'])->name('reports.show');

    // Enrollments
    Route::get('enrollments/export', [EnrollmentController::class, 'export'])->name('enrollments.export');
    Route::resource('enrollments', EnrollmentController::class);
    Route::post('enrollments/{enrollment}/toggle-status', [EnrollmentController::class, 'toggleStatus'])->name('enrollments.toggle-status');

    // Schedules (weekly slots) and the JSON helpers of the schedule form
    Route::resource('schedules', Admin\ScheduleController::class);
    Route::post('schedules/{schedule}/toggle-status', [Admin\ScheduleController::class, 'toggleStatus'])->name('schedules.toggle-status');
    Route::get('schedule-data/teachers/{class}', [ScheduleDataController::class, 'teachers'])->name('schedule-data.teachers');
    Route::get('schedule-data/students/{class}', [ScheduleDataController::class, 'students'])->name('schedule-data.students');

    // Class photo gallery
    Route::resource('class-photos', Admin\ClassPhotoController::class);
    Route::post('class-photos/{photo}/toggle-status', [Admin\ClassPhotoController::class, 'toggleStatus'])->name('class-photos.toggle-status');

    // Certificate templates & bulk generation
    Route::resource('certificate-templates', Admin\CertificateTemplateController::class);
    Route::post('certificate-templates/{template}/toggle-status', [Admin\CertificateTemplateController::class, 'toggleStatus'])->name('certificate-templates.toggle-status');
    Route::get('certificates/bulk-generate', [Admin\CertificateController::class, 'bulkGenerate'])->name('certificates.bulk-generate');
    Route::post('certificates/bulk-generate', [Admin\CertificateController::class, 'processBulkGenerate'])->name('certificates.bulk-generate.process');

    // Testimonials
    Route::resource('testimonials', Admin\TestimonialController::class);
    Route::post('testimonials/{testimonial}/toggle-status', [Admin\TestimonialController::class, 'toggleStatus'])->name('testimonials.toggle-status');
    Route::post('testimonials/{testimonial}/toggle-featured', [Admin\TestimonialController::class, 'toggleFeatured'])->name('testimonials.toggle-featured');

    // Gamification management
    Route::resource('badges', Admin\BadgeController::class);
    Route::post('badges/{badge}/toggle-status', [Admin\BadgeController::class, 'toggleStatus'])->name('badges.toggle-status');
    Route::get('rewards/redemptions', [Admin\RewardController::class, 'redemptions'])->name('rewards.redemptions');
    Route::post('rewards/redemptions/{redemption}/process', [Admin\RewardController::class, 'processRedemption'])->name('rewards.redemptions.process');
    Route::resource('rewards', Admin\RewardController::class);
    Route::post('rewards/{reward}/toggle-status', [Admin\RewardController::class, 'toggleStatus'])->name('rewards.toggle-status');

    // Finance reports (admin view)
    Route::get('finance/dashboard', [Admin\FinanceReportController::class, 'dashboard'])->name('finance.dashboard');
    Route::get('finance/reports', [Admin\FinanceReportController::class, 'reports'])->name('finance.reports');
    Route::get('finance/export', [Admin\FinanceReportController::class, 'export'])->name('finance.export');

    // Roles & permissions (superadmin only)
    Route::middleware(['role:superadmin'])->group(function () {
        Route::resource('roles', Admin\RoleController::class);
        Route::resource('permissions', Admin\PermissionController::class);
    });
});

