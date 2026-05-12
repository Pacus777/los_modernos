<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/emprendedores', function () {
            return Inertia::render('Admin/ComingSoon', [
                'moduleTitle' => 'Emprendedores',
                'moduleDescription' =>
                    'Gestión de emprendedores Wayna: datos, metas y códigos QR.',
            ]);
        })->name('emprendedores.index');

        Route::get('/campanas', function () {
            return Inertia::render('Admin/ComingSoon', [
                'moduleTitle' => 'Campañas',
                'moduleDescription' =>
                    'Campañas de apoyo, metas y acumulado por periodo.',
            ]);
        })->name('campanas.index');

        Route::get('/donaciones', function () {
            return Inertia::render('Admin/ComingSoon', [
                'moduleTitle' => 'Donaciones',
                'moduleDescription' =>
                    'Validación de aportes y seguimiento de estados de pago.',
            ]);
        })->name('donaciones.index');

        Route::get('/trazabilidad', function () {
            return Inertia::render('Admin/ComingSoon', [
                'moduleTitle' => 'Trazabilidad',
                'moduleDescription' =>
                    'Registro de transacciones y trazabilidad de movimientos.',
            ]);
        })->name('trazabilidad.index');

        Route::get('/reportes', function () {
            return Inertia::render('Admin/ComingSoon', [
                'moduleTitle' => 'Reportes',
                'moduleDescription' =>
                    'Reportes de impacto y exportación de datos del sistema.',
            ]);
        })->name('reportes.index');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
