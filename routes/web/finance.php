<?php

/*
|--------------------------------------------------------------------------
| Finance routes (finance, admin, superadmin)
|--------------------------------------------------------------------------
|
| Loaded inside the "auth" middleware group from routes/web.php.
|
*/

use App\Http\Controllers\FinanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['role:finance|admin|superadmin'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('dashboard', [FinanceController::class, 'dashboard'])->name('dashboard');
    Route::get('chart-data', [FinanceController::class, 'getChartDataAjax'])->name('chart-data');

    Route::get('transactions', [FinanceController::class, 'index'])->name('transactions.index');
    Route::get('transactions/create', [FinanceController::class, 'create'])->name('transactions.create');
    Route::post('transactions', [FinanceController::class, 'store'])->name('transactions.store');
    Route::get('transactions/{transaction}/edit', [FinanceController::class, 'edit'])->name('transactions.edit');
    Route::put('transactions/{transaction}', [FinanceController::class, 'update'])->name('transactions.update');
    Route::delete('transactions/{transaction}', [FinanceController::class, 'destroy'])->name('transactions.destroy');
    Route::post('transactions/{transaction}/mark-paid', [FinanceController::class, 'markPaid'])->name('transactions.mark-paid');

    Route::get('reports', [FinanceController::class, 'reports'])->name('reports.index');
    Route::get('reports/daily', [FinanceController::class, 'dailyReport'])->name('reports.daily');
    Route::get('reports/export', [FinanceController::class, 'exportReport'])->name('reports.export');
});
