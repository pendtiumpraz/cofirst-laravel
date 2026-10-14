<?php

/*
|--------------------------------------------------------------------------
| Authenticated account centre: account settings / preferences, privacy centre (my consents, my data export, my requests), sessions.
|--------------------------------------------------------------------------
|
| Loaded inside the auth group. Owned by the account & PDP modules.
| Every route acts on the signed-in user only (no user parameter).
|
*/

use App\Http\Controllers\Account\PreferencesController;
use Illuminate\Support\Facades\Route;

Route::prefix('account')->name('account.')->controller(PreferencesController::class)->group(function () {
    Route::get('preferences', 'edit')->name('preferences');
    Route::put('preferences', 'update')->name('preferences.update');

    // Colour-mode menu of the dashboard shell (resources/js/color-mode.js POSTs {color_mode}).
    Route::post('color-mode', 'colorMode')->middleware('throttle:30,1')->name('color-mode');
});
