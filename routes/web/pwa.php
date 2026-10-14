<?php

/*
|--------------------------------------------------------------------------
| PWA routes (public): web app manifest, offline fallback page.
|--------------------------------------------------------------------------
|
| Owned by the app-shell / PWA module. The service worker itself is the
| static file public/sw.js (served at /sw.js so its scope is the whole site).
|
*/

use App\Http\Controllers\PwaController;
use Illuminate\Support\Facades\Route;

Route::get('/manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/offline', [PwaController::class, 'offline'])->name('pwa.offline');
