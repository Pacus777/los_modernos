<?php

use App\Http\Controllers\Emprendedor\DashboardController;
use App\Http\Controllers\Emprendedor\DonacionHistorialController;
use App\Http\Controllers\Emprendedor\ForcePasswordChangeController;
use App\Http\Controllers\Emprendedor\PerfilController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel emprendedor (E-03 / E-04 / E-05 / E-06 / E-07)
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

        Route::get('/donaciones', [DonacionHistorialController::class, 'index'])
            ->name('donaciones.index');

        Route::get('/donaciones/exportar', [DonacionHistorialController::class, 'exportarExcel'])
            ->name('donaciones.exportar');

        Route::get('/donaciones/exportar/pdf', [DonacionHistorialController::class, 'exportarPdf'])
            ->name('donaciones.exportar.pdf');
    });
