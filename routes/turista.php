<?php

use App\Http\Controllers\Turista\EmprendedorPublicoController;
use Illuminate\Support\Facades\Route;

Route::get('/emprendedor/{id}', [EmprendedorPublicoController::class, 'show'])
    ->name('turista.emprendedor.show');