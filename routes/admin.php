<?php

use App\Http\Controllers\Admin\CampanaController;
use App\Http\Controllers\Admin\DonacionController;
use App\Http\Controllers\Admin\EmprendedorController;
use App\Http\Controllers\Admin\TransaccionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas del panel administrador
|--------------------------------------------------------------------------
|
| Estas rutas son para usuarios autenticados.
| Más adelante se protegerán también con middleware de rol.
|
*/

Route::middleware(['auth', 'verified', 'check.role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Gestión de emprendedores
        |--------------------------------------------------------------------------
        |
        | Route::resource genera automáticamente las rutas:
        |
        | GET    /admin/emprendedores
        | GET    /admin/emprendedores/create
        | POST   /admin/emprendedores
        | GET    /admin/emprendedores/{emprendedor}
        | GET    /admin/emprendedores/{emprendedor}/edit
        | PUT    /admin/emprendedores/{emprendedor}
        | DELETE /admin/emprendedores/{emprendedor}
        |
        */

        Route::resource('emprendedores', EmprendedorController::class)
            ->parameters([
                'emprendedores' => 'emprendedor',
            ]);

        Route::resource('campanas', CampanaController::class)
            ->parameters([
                'campanas' => 'campana',
            ]);

        Route::get('transacciones', [TransaccionController::class, 'index'])
            ->name('transacciones.index');

        Route::get('donaciones', [DonacionController::class, 'index'])
            ->name('donaciones.index');

        Route::patch('donaciones/{donacion}/validar', [DonacionController::class, 'validar'])
            ->name('donaciones.validar');

        Route::patch('donaciones/{donacion}/rechazar', [DonacionController::class, 'rechazar'])
            ->name('donaciones.rechazar');
    });