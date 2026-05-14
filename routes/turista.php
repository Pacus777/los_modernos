<?php

use App\Http\Controllers\Turista\EmprendedorPublicoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Turista\DonacionController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas públicas del turista
|--------------------------------------------------------------------------
|
| Esta ruta no usa auth porque el turista entra desde un QR.
| El QR del emprendedor apuntará a:
| /emprendedor/{id}
|
*/

Route::get('/emprendedor/{id}', [EmprendedorPublicoController::class, 'show'])
    ->name('turista.emprendedor.show');

    Route::post('/donaciones', [DonacionController::class, 'store'])
    ->name('turista.donaciones.store');

    Route::get('/donaciones/confirmacion', function () {
        return Inertia::render('Turista/Confirmacion');
    })->name('turista.donaciones.confirmacion');