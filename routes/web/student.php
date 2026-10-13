<?php

/*
|--------------------------------------------------------------------------
| Student routes
|--------------------------------------------------------------------------
|
| Loaded inside the "auth" middleware group from routes/web.php.
|
*/

use App\Http\Controllers\Student;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('dashboard', [Student\DashboardController::class, 'index'])->name('dashboard');
    Route::get('classes', [StudentController::class, 'classes'])->name('classes');
    Route::get('classes/{class}', [StudentController::class, 'showClass'])->name('classes.show');
    Route::get('courses', [StudentController::class, 'courses'])->name('courses.index');
    Route::get('schedules', [Student\ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('reports', [Student\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{report}', [Student\ReportController::class, 'show'])->name('reports.show');
    Route::get('payments', [StudentController::class, 'payments'])->name('payments');

    // Materials and progress
    Route::get('materials', [Student\MaterialController::class, 'index'])->name('materials.index');
    Route::get('materials/class/{class}', [Student\MaterialController::class, 'byClass'])->name('materials.by-class');
    Route::get('materials/{material}', [Student\MaterialController::class, 'show'])->name('materials.show');
    Route::get('materials/{material}/download', [Student\MaterialController::class, 'download'])->name('materials.download');
    Route::get('progress/class/{class}', [Student\MaterialController::class, 'progress'])->name('progress.class');

    // Curriculum
    Route::get('curriculum', [Student\CurriculumController::class, 'index'])->name('curriculum.index');
    Route::get('curriculum/class/{class}', [Student\CurriculumController::class, 'show'])->name('curriculum.show');
});
