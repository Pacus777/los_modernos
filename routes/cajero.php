<?php

use App\Http\Controllers\Cajero\PendienteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas del panel cajero
|--------------------------------------------------------------------------
|
| Estas rutas son accesibles para:
| - cajero
| - admin
|
| El cajero solo debe ver la pantalla de confirmación de pagos en efectivo.
|
*/

Route::middleware(['auth', 'verified', 'check.role:admin,cajero', 'nocache'])
    ->prefix('cajero')
    ->name('cajero.')
    ->group(function () {
        Route::get('/efectivo', [PendienteController::class, 'index'])
            ->name('efectivo');

        Route::get('/efectivo/pendientes', [PendienteController::class, 'index'])
            ->name('efectivo.pendientes');

        Route::get('/efectivo/{donacion}/confirmar', [PendienteController::class, 'confirmarForm'])
            ->name('efectivo.confirmar');

        Route::post('/efectivo/{donacion}/confirmar', [PendienteController::class, 'confirmar'])
            ->name('efectivo.confirmar.store');
    });