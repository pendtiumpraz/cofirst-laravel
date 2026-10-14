<?php

/*
|--------------------------------------------------------------------------
| Shared authenticated routes (all roles)
|--------------------------------------------------------------------------
|
| Loaded inside the "auth" middleware group from routes/web.php.
| Note: static segments (e.g. "featured", "create") must be registered
| BEFORE resource/show routes with a wildcard, or the wildcard swallows them.
|
*/

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClassReportController;
use App\Http\Controllers\GamificationController;
use App\Http\Controllers\PhotoUploadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectGalleryController;
use Illuminate\Support\Facades\Route;

// Profile
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])
    ->middleware('throttle:5,1,profile-destroy')
    ->name('profile.destroy');
Route::post('/profile/photo', [PhotoUploadController::class, 'uploadProfilePhoto'])
    ->middleware('throttle:10,1,profile-photo')
    ->name('profile.photo.upload');
Route::delete('/profile/photo', [PhotoUploadController::class, 'deleteProfilePhoto'])->name('profile.photo.delete');
Route::delete('/profile/sessions', [ProfileController::class, 'destroyOtherSessions'])
    ->middleware('throttle:6,1,profile-sessions')
    ->name('profile.sessions.destroy');

// Project gallery (students, teachers, parents, admins)
Route::middleware(['role:student|teacher|parent|admin|superadmin'])->group(function () {
    Route::get('project-gallery/featured', [ProjectGalleryController::class, 'featured'])->name('project-gallery.featured');
    Route::resource('project-gallery', ProjectGalleryController::class);
});

// Class reports (admin, superadmin, teacher)
Route::middleware(['role:admin|superadmin|teacher'])->group(function () {
    Route::get('class-reports/get-schedules-and-students', [ClassReportController::class, 'getSchedulesAndStudents'])->name('class-reports.get-schedules-and-students');
    Route::resource('class-reports', ClassReportController::class);
});

// Certificate management (admin and teachers) — before the {certificate} wildcard
Route::middleware(['role:admin|superadmin|teacher'])->group(function () {
    Route::get('certificates/create', [CertificateController::class, 'create'])->name('certificates.create');
    Route::post('certificates', [CertificateController::class, 'store'])->name('certificates.store');
    Route::get('certificates/bulk/create', [CertificateController::class, 'bulkCreate'])->name('certificates.bulk-create');
    Route::post('certificates/bulk', [CertificateController::class, 'bulkStore'])->name('certificates.bulk-store');
});

// Certificate viewing (role-filtered inside the controller)
Route::middleware(['role:admin|superadmin|teacher|student|parent'])->group(function () {
    Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
    Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
});

// Certificate invalidation (admin only)
Route::middleware(['role:admin|superadmin'])->group(function () {
    Route::patch('certificates/{certificate}/invalidate', [CertificateController::class, 'invalidate'])->name('certificates.invalidate');
});

// Chat
Route::prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::post('/start', [ChatController::class, 'startConversation'])->name('start');
    Route::get('/search', [ChatController::class, 'search'])->name('search');
    Route::get('/conversation/{conversation}', [ChatController::class, 'show'])->name('show');
    Route::post('/conversation/{conversation}/send', [ChatController::class, 'sendMessage'])->name('send');
    Route::post('/conversation/{conversation}/mark-read', [ChatController::class, 'markAsRead'])->name('mark-read');
    Route::put('/message/{message}', [ChatController::class, 'editMessage'])->name('message.edit');
    Route::delete('/message/{message}', [ChatController::class, 'deleteMessage'])->name('message.delete');
});

// Gamification
Route::prefix('gamification')->name('gamification.')->group(function () {
    Route::get('/', [GamificationController::class, 'index'])->name('index');
    Route::get('/leaderboard', [GamificationController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/badges', [GamificationController::class, 'badges'])->name('badges');
    Route::post('/badges/{badge}/toggle-featured', [GamificationController::class, 'toggleBadgeFeatured'])->name('badges.toggle-featured');
    Route::get('/rewards', [GamificationController::class, 'rewards'])->name('rewards');
    Route::post('/rewards/{reward}/redeem', [GamificationController::class, 'redeemReward'])->name('rewards.redeem');
    Route::get('/redemptions', [GamificationController::class, 'redemptions'])->name('redemptions');
    Route::get('/point-history', [GamificationController::class, 'pointHistory'])->name('point-history');
});
