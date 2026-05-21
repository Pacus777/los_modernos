<?php

use App\Http\Controllers\Admin\TwoFactorSettingsController;
use App\Http\Controllers\Admin\CampanaController;
use App\Http\Controllers\Admin\DonacionController;
use App\Http\Controllers\Admin\EmprendedorCuentaController;
use App\Http\Controllers\Admin\EmprendedorController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\PuntoController;

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

Route::middleware(['auth', 'verified', 'check.role:admin', 'admin.session', 'nocache'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('seguridad/2fa', [TwoFactorSettingsController::class, 'edit'])
            ->name('two-factor.edit');
        Route::post('seguridad/2fa/preparar', [TwoFactorSettingsController::class, 'preparar'])
            ->name('two-factor.preparar');
        Route::post('seguridad/2fa/activar', [TwoFactorSettingsController::class, 'activar'])
            ->name('two-factor.activar');
        Route::delete('seguridad/2fa', [TwoFactorSettingsController::class, 'desactivar'])
            ->name('two-factor.desactivar');
    });

Route::middleware(['auth', 'verified', 'check.role:admin', 'admin.session', 'admin.two_factor', 'nocache'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::post('sesion/tocar', function () {
            session(['admin_last_activity' => time()]);

            return back();
        })->name('session.touch');

        Route::redirect('/', '/admin/dashboard');

        Route::get('/dashboard', [ReporteController::class, 'impacto'])
            ->name('dashboard');

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

        Route::get('emprendedores/{emprendedor}/finalizar', [EmprendedorController::class, 'finalizar'])
            ->name('emprendedores.finalizar');

        Route::post('emprendedores/{emprendedor}/completar-registro', [EmprendedorController::class, 'completarRegistro'])
            ->name('emprendedores.completar-registro');

        Route::resource('emprendedores', EmprendedorController::class)
            ->parameters([
                'emprendedores' => 'emprendedor',
            ]);

        Route::post('emprendedores/{emprendedor}/generar-qr', [EmprendedorController::class, 'generarQr'])
            ->name('emprendedores.generar-qr');

        Route::post('emprendedores/{emprendedor}/cuenta', [EmprendedorCuentaController::class, 'store'])
            ->name('emprendedores.cuenta.store');

        Route::post('emprendedores/{emprendedor}/cuenta/reenviar', [EmprendedorCuentaController::class, 'reenviar'])
            ->name('emprendedores.cuenta.reenviar');

        Route::resource('campanas', CampanaController::class)
            ->parameters([
                'campanas' => 'campana',
            ]);

        /*
        | T-A20: la vista de trazabilidad no está en el menú del admin común.
        | El registro interno sigue en TraceabilityService. Cuando exista rol
        | superadmin, publicar aquí con check.role:superadmin:
        |
        | Route::get('transacciones', [\App\Http\Controllers\Admin\TransaccionController::class, 'index'])
        |     ->name('transacciones.index');
        */

        Route::get('donaciones', [DonacionController::class, 'index'])
            ->name('donaciones.index');

        Route::get('donaciones/exportar-csv', [DonacionController::class, 'exportarCsv'])
            ->name('donaciones.exportar-csv');

        Route::patch('donaciones/revision-masiva', [DonacionController::class, 'revisionMasiva'])
            ->name('donaciones.revision-masiva');

        Route::patch('donaciones/{donacion}/validar', [DonacionController::class, 'validar'])
            ->name('donaciones.validar');

        Route::patch('donaciones/{donacion}/confirmar', [DonacionController::class, 'validar'])
            ->name('donaciones.confirmar');

        Route::patch('donaciones/{donacion}/rechazar', [DonacionController::class, 'rechazar'])
            ->name('donaciones.rechazar');


        Route::get('/reportes', [ReporteController::class, 'donaciones'])
            ->name('reportes.index');

        Route::resource('puntos', PuntoController::class)
            ->parameters([
                'puntos' => 'punto',
            ]);
    });