<?php

/*
|--------------------------------------------------------------------------
| UI kit preview (admin, superadmin) — living documentation of the design
| system components in both themes and in dark mode.
|--------------------------------------------------------------------------
|
| Loaded inside the auth group. Owned by the design-system module.
| Preview switches: /admin/ui-kit?theme=b&mode=dark&density=compact&stat=cards&errors=1
|
*/

use App\Http\Controllers\UiKitController;
use Illuminate\Support\Facades\Route;

Route::middleware('role:admin|superadmin')
    ->get('/admin/ui-kit', [UiKitController::class, 'index'])
    ->name('admin.ui-kit');
