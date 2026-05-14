<?php

use App\Http\Controllers\Turista\EmprendedorPublicoController;
use Illuminate\Support\Facades\Route;

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