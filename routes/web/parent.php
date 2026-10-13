<?php

/*
|--------------------------------------------------------------------------
| Parent routes
|--------------------------------------------------------------------------
|
| Loaded inside the "auth" middleware group from routes/web.php.
|
*/

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['role:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('dashboard', function () {
        $children = Auth::user()->children()->with('studentReports', 'enrollments.className')->get();

        $stats = [
            'total_children' => $children->count(),
            'active_classes' => $children->sum(fn ($child) => $child->enrollments->where('status', 'active')->count()),
            'available_classes' => \App\Models\ClassName::where('is_active', true)
                ->whereIn('status', ['planned', 'active'])
                ->count(),
            'total_spent' => $children->sum(fn ($child) => $child->enrollments->sum(
                fn ($enrollment) => $enrollment->className->course->price ?? 0
            )),
        ];

        $purchasedClasses = collect();
        foreach ($children as $child) {
            foreach ($child->enrollments as $enrollment) {
                if ($enrollment->status === 'active' && $enrollment->className) {
                    $purchasedClasses->push([
                        'child' => $child,
                        'class' => $enrollment->className,
                        'enrollment' => $enrollment,
                    ]);
                }
            }
        }

        $availableClasses = \App\Models\ClassName::where('is_active', true)
            ->whereIn('status', ['planned', 'active'])
            ->with('course', 'teachers')
            ->get();

        $childrenIds = $children->pluck('id');
        $schedules = \App\Models\Schedule::whereHas('enrollment', fn ($q) => $q->whereIn('student_id', $childrenIds))
            ->where('is_active', true)
            ->with(['enrollment.student', 'className'])
            ->get();

        // Schedules use 1=Monday ... 7=Sunday
        $todayDayOfWeek = now()->dayOfWeekIso;
        $todaySchedules = $schedules->where('day_of_week', $todayDayOfWeek)->values();

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

    // Child progress tracking
    Route::get('progress', [\App\Http\Controllers\Parent\ProgressController::class, 'index'])->name('progress.index');
    Route::get('progress/child/{child}', [\App\Http\Controllers\Parent\ProgressController::class, 'child'])->name('progress.child');
    Route::get('progress/child/{child}/materials/{class}', [\App\Http\Controllers\Parent\ProgressController::class, 'childMaterials'])->name('progress.child-materials');
    Route::get('progress/child/{child}/summary', [\App\Http\Controllers\Parent\ProgressController::class, 'getChildProgressSummary'])->name('progress.child-summary');

    Route::get('child/{student}/reports', [StudentController::class, 'childReports'])->name('child-reports');
    Route::get('child/{student}/schedule', [StudentController::class, 'childSchedule'])->name('child-schedule');
    Route::get('child/{student}/payments', [StudentController::class, 'childPayments'])->name('child-payments');

    // Curriculum
    Route::get('curriculum', [\App\Http\Controllers\Parent\CurriculumController::class, 'index'])->name('curriculum.index');
    Route::get('curriculum/child/{child}/class/{class}', [\App\Http\Controllers\Parent\CurriculumController::class, 'showChildProgress'])->name('curriculum.child-progress');

    // Class photos
    Route::get('class-photos', [\App\Http\Controllers\Parent\ClassPhotoController::class, 'index'])->name('class-photos.index');
    Route::get('class-photos/{photo}', [\App\Http\Controllers\Parent\ClassPhotoController::class, 'show'])->name('class-photos.show');

    // Testimonials
    Route::get('testimonials', [\App\Http\Controllers\Parent\TestimonialController::class, 'index'])->name('testimonials.index');
    Route::get('testimonials/create', [\App\Http\Controllers\Parent\TestimonialController::class, 'create'])->name('testimonials.create');
    Route::post('testimonials', [\App\Http\Controllers\Parent\TestimonialController::class, 'store'])->name('testimonials.store');

    // Contact admin
    Route::get('contact-admin', [\App\Http\Controllers\Parent\ContactController::class, 'index'])->name('contact-admin');
    Route::post('contact-admin', [\App\Http\Controllers\Parent\ContactController::class, 'send'])->name('contact-admin.send');
});
