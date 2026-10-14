<?php

/*
|--------------------------------------------------------------------------
| Public privacy & legal pages (UU PDP): privacy notice, terms, cookie policy, DPO contact, public data-subject-request form, cookie-consent endpoint.
|--------------------------------------------------------------------------
|
| Loaded OUTSIDE the auth group (public). Owned by the PDP consent & DSR modules.
|
*/

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Consent & policies (PDP consent module)
|--------------------------------------------------------------------------
| Versioned policy pages, cookie preferences, parental consent links and the
| policy re-acceptance page. Static segments are registered before wildcards.
*/
Route::get('/kebijakan-privasi', [\App\Http\Controllers\Privacy\PolicyController::class, 'privacy'])->name('privacy.notice');
Route::get('/kebijakan-privasi/riwayat', [\App\Http\Controllers\Privacy\PolicyController::class, 'history'])->name('privacy.history');
Route::get('/kebijakan-privasi/riwayat/{policy}', [\App\Http\Controllers\Privacy\PolicyController::class, 'version'])
    ->whereNumber('policy')->name('privacy.history.show');
Route::get('/syarat-ketentuan', [\App\Http\Controllers\Privacy\PolicyController::class, 'terms'])->name('legal.terms');
Route::get('/kebijakan-cookie', [\App\Http\Controllers\Privacy\PolicyController::class, 'cookies'])->name('privacy.cookies');
Route::get('/privasi-anak', [\App\Http\Controllers\Privacy\PolicyController::class, 'children'])->name('privacy.children');

Route::get('/pengaturan-cookie', [\App\Http\Controllers\Privacy\CookieConsentController::class, 'edit'])->name('privacy.cookies.preferences');
Route::post('/pengaturan-cookie', [\App\Http\Controllers\Privacy\CookieConsentController::class, 'update'])
    ->middleware('throttle:30,1')->name('privacy.cookies.update');

Route::post('/persetujuan-orang-tua/kirim-ulang', [\App\Http\Controllers\Privacy\ParentalConsentController::class, 'resend'])
    ->middleware('throttle:3,10')->name('privacy.parental-consent.resend');
Route::get('/persetujuan-orang-tua/{token}', [\App\Http\Controllers\Privacy\ParentalConsentController::class, 'show'])
    ->middleware('throttle:30,1')->name('privacy.parental-consent.show');
Route::post('/persetujuan-orang-tua/{token}', [\App\Http\Controllers\Privacy\ParentalConsentController::class, 'respond'])
    ->middleware('throttle:10,1')->name('privacy.parental-consent.respond');

Route::middleware('auth')->group(function () {
    Route::get('/persetujuan-kebijakan', [\App\Http\Controllers\Privacy\PolicyAcceptanceController::class, 'show'])->name('privacy.accept');
    Route::post('/persetujuan-kebijakan', [\App\Http\Controllers\Privacy\PolicyAcceptanceController::class, 'store'])->name('privacy.accept.store');
});
