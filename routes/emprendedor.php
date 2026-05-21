<?php

use App\Http\Controllers\Emprendedor\DashboardController;
use App\Http\Controllers\Emprendedor\ForcePasswordChangeController;
use App\Http\Controllers\Emprendedor\PerfilController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel emprendedor (E-03 / E-04 / E-05 / E-06)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'check.role:emprendedor', 'nocache'])
    ->prefix('emprendedor')
    ->name('emprendedor.')
    ->group(function () {
        Route::get('password/obligatorio', [ForcePasswordChangeController::class, 'create'])
            ->name('password.force');

        Route::post('password/obligatorio', [ForcePasswordChangeController::class, 'store'])
            ->name('password.force.store');
    });

Route::middleware(['auth', 'verified', 'check.role:emprendedor', 'emprendedor.force_password', 'nocache'])
    ->prefix('emprendedor')
    ->name('emprendedor.')
    ->group(function () {
        Route::redirect('/', '/emprendedor/dashboard');
        Route::redirect('/panel', '/emprendedor/dashboard');

        Route::get('/dashboard', DashboardController::class)
            ->name('dashboard');

        Route::get('/perfil/editar', [PerfilController::class, 'edit'])
            ->name('perfil.edit');

        Route::put('/perfil', [PerfilController::class, 'update'])
            ->name('perfil.update');
    });
