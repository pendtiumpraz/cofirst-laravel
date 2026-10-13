<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Public pages live here. Every authenticated area has its own file under
| routes/web/ (one file per role / module) so modules can evolve
| independently. Route names are stable — views depend on them.
|
*/

// Public website
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/about', [LandingController::class, 'about'])->name('about');
Route::get('/courses', [LandingController::class, 'courses'])->name('courses');
Route::get('/contact', [LandingController::class, 'contact'])->name('contact');

// Certificate verification (public)
Route::get('/certificate/verify/{code}', [CertificateController::class, 'verify'])->name('certificate.verify');

// PWA (manifest, offline page) and public privacy pages
require __DIR__.'/web/pwa.php';
require __DIR__.'/web/privacy.php';

Route::middleware('auth')->group(function () {
    // Role switching for users that hold more than one role
    Route::get('/role-selection', [\App\Http\Controllers\RoleSwitchController::class, 'showRoleSelection'])->name('role.selection');
    Route::post('/role-selection', [\App\Http\Controllers\RoleSwitchController::class, 'setActiveRole'])->name('role.set');
    Route::post('/switch-role', [\App\Http\Controllers\RoleSwitchController::class, 'switchRole'])->name('role.switch');

    // Main dashboard entry point: redirect to the dashboard of the user's role
    Route::get('/dashboard', function () {
        $user = Auth::user();

        $targets = [
            'superadmin' => 'admin.dashboard',
            'admin' => 'admin.dashboard',
            'teacher' => 'teacher.dashboard',
            'parent' => 'parent.dashboard',
            'student' => 'student.dashboard',
            'finance' => 'finance.dashboard',
        ];

        // Reason: honour the role chosen on the role-selection screen first.
        $active = session('active_role');
        if ($active && isset($targets[$active]) && $user->hasRole($active)) {
            return redirect()->route($targets[$active]);
        }

        foreach ($targets as $role => $route) {
            if ($user->hasRole($role)) {
                return redirect()->route($route);
            }
        }

        return redirect()->route('login');
    })->name('dashboard');

    require __DIR__.'/web/shared.php';
    require __DIR__.'/web/account.php';
    require __DIR__.'/web/account-privacy.php';
    require __DIR__.'/web/admin.php';
    require __DIR__.'/web/settings.php';
    require __DIR__.'/web/uikit.php';
    require __DIR__.'/web/privacy-admin.php';
    require __DIR__.'/web/teacher.php';
    require __DIR__.'/web/parent.php';
    require __DIR__.'/web/student.php';
    require __DIR__.'/web/finance.php';
});

require __DIR__.'/auth.php';
