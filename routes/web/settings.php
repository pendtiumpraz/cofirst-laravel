<?php

/*
|--------------------------------------------------------------------------
| Admin settings module (application-wide configuration and A/B version toggles).
|--------------------------------------------------------------------------
|
| Loaded inside the auth group. Owned by the settings module.
| Admin & superadmin may open every group; groups with edit_roles in
| config/settings.php (Keamanan, Privasi & PDP) are read-only for admins.
| Static segments are registered before the {group} wildcard.
|
*/

use App\Http\Controllers\Admin\SettingsController;
use App\Support\Settings\Http\ConfirmPasswordForSensitiveGroups;
use Illuminate\Support\Facades\Route;

Route::middleware('role:admin|superadmin')
    ->prefix('admin/settings')
    ->name('admin.settings.')
    ->controller(SettingsController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');

        // "Pratinjau": session-only preview of an A/B appearance option.
        Route::get('preview', 'preview')->middleware('signed')->name('preview');
        Route::get('preview/auth', 'previewAuth')->name('preview.auth');
        Route::post('preview/apply', 'applyPreview')->name('preview.apply');
        Route::delete('preview', 'endPreview')->name('preview.destroy');

        $groups = array_keys(config('settings.groups', []));

        Route::middleware(ConfirmPasswordForSensitiveGroups::class)->group(function () use ($groups) {
            Route::get('{group}', 'edit')->whereIn('group', $groups)->name('edit');
            Route::put('{group}', 'update')->whereIn('group', $groups)->name('update');
            Route::post('{group}/reset', 'reset')->whereIn('group', $groups)->name('reset');
        });
    });
