<?php

use App\Http\Controllers\Emprendedor\DashboardController;
use App\Http\Controllers\Emprendedor\DonacionHistorialController;
use App\Http\Controllers\Emprendedor\EmprendedorNotificacionController;
use App\Http\Controllers\Emprendedor\EmprendedorMetaController;
use App\Http\Controllers\Emprendedor\ForcePasswordChangeController;
use App\Http\Controllers\Emprendedor\NotificacionPreferenciasController;
use App\Http\Controllers\Emprendedor\PerfilController;
use App\Http\Controllers\Emprendedor\EmprendedorPostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel emprendedor (E-03 … E-09)
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

        Route::get('/perfil/editar', [PerfilController::class, 'edit'])
            ->name('perfil.edit');

        Route::put('/perfil', [PerfilController::class, 'update'])
            ->name('perfil.update');
    });

Route::middleware(['auth', 'verified', 'check.role:emprendedor', 'emprendedor.force_password', 'emprendedor.profile_complete', 'nocache'])
    ->prefix('emprendedor')
    ->name('emprendedor.')
    ->group(function () {
        Route::get('/dashboard', DashboardController::class)
            ->name('dashboard');

        Route::get('/publicaciones', [EmprendedorPostController::class, 'index'])
            ->name('publicaciones.index');

        Route::post('/publicaciones', [EmprendedorPostController::class, 'store'])
            ->name('publicaciones.store');

        Route::put('/publicaciones/{post}', [EmprendedorPostController::class, 'update'])
            ->name('publicaciones.update');

        Route::delete('/publicaciones/{post}', [EmprendedorPostController::class, 'destroy'])
            ->name('publicaciones.destroy');

        Route::get('/preferencias', [NotificacionPreferenciasController::class, 'edit'])
            ->name('preferencias.edit');

        Route::put('/preferencias', [NotificacionPreferenciasController::class, 'update'])
            ->name('preferencias.update');

        Route::get('/notificaciones', [EmprendedorNotificacionController::class, 'index'])
            ->name('notificaciones.index');

        Route::patch('/notificaciones/{notificacion}/leer', [EmprendedorNotificacionController::class, 'marcarLeida'])
            ->name('notificaciones.leer');

        Route::post('/notificaciones/marcar-todas', [EmprendedorNotificacionController::class, 'marcarTodasLeidas'])
            ->name('notificaciones.marcar-todas');

        Route::get('/donaciones', [DonacionHistorialController::class, 'index'])
            ->name('donaciones.index');

        Route::get('/donaciones/exportar', [DonacionHistorialController::class, 'exportarExcel'])
            ->name('donaciones.exportar');

        Route::get('/donaciones/exportar/pdf', [DonacionHistorialController::class, 'exportarPdf'])
            ->name('donaciones.exportar.pdf');

        Route::get('/mis-metas', [EmprendedorMetaController::class, 'index'])
            ->name('mis-metas.index');

        Route::get('/meta/crear', [EmprendedorMetaController::class, 'create'])
            ->name('meta.create');

        Route::post('/meta', [EmprendedorMetaController::class, 'store'])
            ->name('meta.store');

        Route::get('/meta/editar', [EmprendedorMetaController::class, 'edit'])
            ->name('meta.edit');

        Route::put('/meta/{campana}', [EmprendedorMetaController::class, 'update'])
            ->name('meta.update');

        Route::post('/meta/{campana}/cerrar', [EmprendedorMetaController::class, 'close'])
            ->name('meta.close');
    });
