<?php

use App\Http\Controllers\Cajero\PendienteController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'check.role:admin,cajero'])
    ->prefix('cajero')
    ->name('cajero.')
    ->group(function () {
        Route::get('/efectivo', function () {
            return Inertia::render('Cajero/Efectivo');
        })->name('efectivo');

        Route::get('/efectivo/pendientes', [PendienteController::class, 'index'])
            ->name('efectivo.pendientes');

        Route::get('/efectivo/{donacion}/confirmar', [PendienteController::class, 'confirmarForm'])
            ->name('efectivo.confirmar');

        Route::post('/efectivo/{donacion}/confirmar', [PendienteController::class, 'confirmar'])
            ->name('efectivo.confirmar.store');
    });