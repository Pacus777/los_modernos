<?php

use App\Http\Controllers\Turista\DonacionConfirmacionController;
use App\Http\Controllers\Turista\EmprendedorPublicoController;
use App\Http\Controllers\Turista\DonacionController;
use App\Http\Controllers\Turista\PuntoController;
use App\Http\Controllers\Turista\ChatController;

use Illuminate\Support\Facades\Route;
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
    ->middleware('prevent.duplicate.payment')
    ->name('turista.donaciones.store');

    Route::get('/donaciones/confirmacion', function () {
        return Inertia::render('Turista/Confirmacion', [
            'confirmacion' => null,
            'qr_pago_url' => null,
            'success' => null,
        ]);
    });

    Route::get('/donaciones/confirmacion/{donacion}', [DonacionConfirmacionController::class, 'show'])
        ->name('turista.donaciones.confirmacion');

    Route::get('/punto/{slug}', [PuntoController::class, 'show'])
        ->name('punto.show');

    Route::post('/chat', [ChatController::class, 'store'])
        ->name('chat.store');