<?php

/*
|--------------------------------------------------------------------------
| Privacy / PDP back-office for admins & DPO: DSR handling, consent purposes, policy documents, ROPA, processors & DPA, DPIA, breach log, audit log, retention.
|--------------------------------------------------------------------------
|
| Loaded inside the auth group. Owned by the PDP governance modules.
|
*/

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Consent & policies back-office (PDP consent module)
|--------------------------------------------------------------------------
| Policy documents need 'manage legal documents'; consent purposes 'manage privacy';
| the consent ledger is also readable with 'handle data requests' (DSR handling).
*/
Route::prefix('admin/privasi')->name('admin.privacy.')->group(function () {
    Route::middleware('permission:manage legal documents')->group(function () {
        Route::get('kebijakan', [\App\Http\Controllers\Admin\Privacy\PolicyDocumentController::class, 'index'])->name('policies.index');
        Route::get('kebijakan/buat', [\App\Http\Controllers\Admin\Privacy\PolicyDocumentController::class, 'create'])->name('policies.create');
        Route::post('kebijakan', [\App\Http\Controllers\Admin\Privacy\PolicyDocumentController::class, 'store'])->name('policies.store');
        Route::get('kebijakan/{policy}', [\App\Http\Controllers\Admin\Privacy\PolicyDocumentController::class, 'show'])->whereNumber('policy')->name('policies.show');
        Route::get('kebijakan/{policy}/ubah', [\App\Http\Controllers\Admin\Privacy\PolicyDocumentController::class, 'edit'])->whereNumber('policy')->name('policies.edit');
        Route::put('kebijakan/{policy}', [\App\Http\Controllers\Admin\Privacy\PolicyDocumentController::class, 'update'])->whereNumber('policy')->name('policies.update');
        Route::delete('kebijakan/{policy}', [\App\Http\Controllers\Admin\Privacy\PolicyDocumentController::class, 'destroy'])->whereNumber('policy')->name('policies.destroy');
        Route::post('kebijakan/{policy}/terbitkan', [\App\Http\Controllers\Admin\Privacy\PolicyDocumentController::class, 'publish'])->whereNumber('policy')->name('policies.publish');
        Route::get('kebijakan/{policy}/bandingkan', [\App\Http\Controllers\Admin\Privacy\PolicyDocumentController::class, 'diff'])->whereNumber('policy')->name('policies.diff');
    });

    Route::middleware('permission:manage privacy')->group(function () {
        Route::get('tujuan-persetujuan', [\App\Http\Controllers\Admin\Privacy\ConsentPurposeController::class, 'index'])->name('consent-purposes.index');
        Route::get('tujuan-persetujuan/buat', [\App\Http\Controllers\Admin\Privacy\ConsentPurposeController::class, 'create'])->name('consent-purposes.create');
        Route::post('tujuan-persetujuan', [\App\Http\Controllers\Admin\Privacy\ConsentPurposeController::class, 'store'])->name('consent-purposes.store');
        Route::get('tujuan-persetujuan/{purpose}/ubah', [\App\Http\Controllers\Admin\Privacy\ConsentPurposeController::class, 'edit'])->whereNumber('purpose')->name('consent-purposes.edit');
        Route::put('tujuan-persetujuan/{purpose}', [\App\Http\Controllers\Admin\Privacy\ConsentPurposeController::class, 'update'])->whereNumber('purpose')->name('consent-purposes.update');
        Route::delete('tujuan-persetujuan/{purpose}', [\App\Http\Controllers\Admin\Privacy\ConsentPurposeController::class, 'destroy'])->whereNumber('purpose')->name('consent-purposes.destroy');
    });

    Route::middleware('permission:manage privacy|handle data requests')->group(function () {
        Route::get('catatan-persetujuan', [\App\Http\Controllers\Admin\Privacy\ConsentRecordController::class, 'index'])->name('consent-records.index');
        Route::get('catatan-persetujuan/ekspor', [\App\Http\Controllers\Admin\Privacy\ConsentRecordController::class, 'export'])
            ->middleware('throttle:10,1')->name('consent-records.export');
    });
});
