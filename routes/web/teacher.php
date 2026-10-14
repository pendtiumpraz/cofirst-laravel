<?php

/*
|--------------------------------------------------------------------------
| Teacher routes
|--------------------------------------------------------------------------
|
| Loaded inside the "auth" middleware group from routes/web.php.
|
*/

use App\Http\Controllers\ReportController;
use App\Http\Controllers\Teacher;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::middleware(['role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('dashboard', [Teacher\DashboardController::class, 'index'])->name('dashboard');
    Route::get('classes', [TeacherController::class, 'classes'])->name('classes');
    Route::get('classes/{class}', [TeacherController::class, 'classDetail'])->name('class-detail');
    Route::get('students', [TeacherController::class, 'students'])->name('students.index');
    Route::get('schedules', [TeacherController::class, 'schedule'])->name('schedules.index');

    // Curriculum (read-only) + per-class meeting progress
    Route::get('curriculums', [Teacher\CurriculumController::class, 'index'])->name('curriculums.index');
    Route::get('curriculums/class/{class}/progress', [Teacher\CurriculumController::class, 'classProgress'])->name('curriculums.class-progress');
    Route::post('curriculums/class/{class}/progress', [Teacher\CurriculumController::class, 'updateClassProgress'])->name('curriculums.class-progress.update');
    Route::get('curriculums/{curriculum}', [Teacher\CurriculumController::class, 'show'])->name('curriculums.show');
    Route::get('curriculums/{curriculum}/syllabuses', [Teacher\CurriculumController::class, 'syllabuses'])->name('curriculums.syllabuses');
    Route::get('curriculums/{curriculum}/materials', [Teacher\CurriculumController::class, 'materials'])->name('curriculums.materials');

    // Syllabuses (read-only)
    Route::get('syllabuses', [Teacher\SyllabusController::class, 'index'])->name('syllabuses.index');
    Route::get('syllabuses/{syllabus}', [Teacher\SyllabusController::class, 'show'])->name('syllabuses.show');

    // Materials (read-only)
    Route::get('materials', [Teacher\MaterialController::class, 'index'])->name('materials.index');
    Route::get('materials/{material}', [Teacher\MaterialController::class, 'show'])->name('materials.show');
    Route::get('materials/{material}/download', [Teacher\MaterialController::class, 'download'])->name('materials.download');

    // Student reports
    Route::get('reports/class/{class}', [ReportController::class, 'byClass'])->name('reports.by-class');
    Route::get('reports/student/{student}', [ReportController::class, 'byStudent'])->name('reports.by-student');
    Route::resource('reports', ReportController::class);

    // Teacher handovers
    Route::resource('handovers', Teacher\HandoverController::class);
    Route::post('handovers/{handover}/approve', [Teacher\HandoverController::class, 'approve'])->name('handovers.approve');
    Route::post('handovers/{handover}/reject', [Teacher\HandoverController::class, 'reject'])->name('handovers.reject');
    Route::post('handovers/{handover}/complete', [Teacher\HandoverController::class, 'complete'])->name('handovers.complete');

    // Class photos
    Route::get('class-photos/upload/{class}', [Teacher\ClassPhotoController::class, 'upload'])->name('class-photos.upload');
    Route::resource('class-photos', Teacher\ClassPhotoController::class);

    // Certificates
    Route::get('certificates/generate', [Teacher\CertificateController::class, 'generate'])->name('certificates.generate');
    Route::post('certificates/generate', [Teacher\CertificateController::class, 'processGenerate'])->name('certificates.process-generate');

    // Student progress (gamification)
    Route::get('student-progress', [Teacher\StudentProgressController::class, 'index'])->name('student-progress.index');
    Route::get('student-progress/{student}', [Teacher\StudentProgressController::class, 'show'])->name('student-progress.show');

    // Attendance
    Route::get('attendance', [Teacher\AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/today', [Teacher\AttendanceController::class, 'today'])->name('attendance.today');
    Route::get('attendance/export', [Teacher\AttendanceController::class, 'export'])->name('attendance.export');
    Route::get('attendance/classes/{class}', [Teacher\AttendanceController::class, 'take'])->name('attendance.take');
    Route::post('attendance/classes/{class}', [Teacher\AttendanceController::class, 'store'])->name('attendance.store');
});
