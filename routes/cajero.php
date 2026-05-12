<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas del panel cajero
|--------------------------------------------------------------------------
|
| El cajero entra directamente a esta pantalla.
| Más adelante esta ruta se protegerá con CheckRole.
|
*/

Route::middleware(['auth', 'verified'])
    ->prefix('cajero')
    ->name('cajero.')
    ->group(function () {
        Route::get('/efectivo', function () {
            return Inertia::render('Cajero/Efectivo');
        })->name('efectivo');
    });