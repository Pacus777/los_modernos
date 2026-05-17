<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Turista\ExplorarController;
use App\Support\AuthRedirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\LocaleController;


//esto y Turista/Prueba.jsx son solo para probar el sistema de traducciones, luego se eliminarán
Route::get('/turista-prueba', function () {
    return Inertia::render('Turista/Prueba');
})->name('turista.prueba');


Route::post('/idioma', [LocaleController::class, 'update'])
    ->name('locale.update');


Route::get('/', ExplorarController::class)->name('turista.explorar');

Route::get('/dashboard', function (Request $request) {
    return redirect()->to(
        AuthRedirect::homeRouteForRole($request->user()?->rol?->nombre),
    );
})->middleware(['auth', 'verified', 'nocache'])->name('dashboard');

Route::middleware(['auth', 'nocache'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| Rutas separadas del sistema WAYNA
|--------------------------------------------------------------------------
|
| Mantenemos las rutas administrativas y de cajero en archivos separados
| para que web.php no crezca demasiado.
|
*/

require __DIR__.'/admin.php';
require __DIR__.'/cajero.php';

require __DIR__.'/auth.php';
