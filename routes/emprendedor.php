<?php

use App\Http\Controllers\Emprendedor\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel emprendedor (E-03 / base E-04)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'check.role:emprendedor', 'nocache'])
    ->prefix('emprendedor')
    ->name('emprendedor.')
    ->group(function () {
        Route::redirect('/', '/emprendedor/panel');

        Route::get('/panel', DashboardController::class)
            ->name('dashboard');
    });
