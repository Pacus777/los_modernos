<?php

use App\Http\Controllers\Webhook\LibelulaWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhooks de pasarelas (sin sesión; CSRF excluido en bootstrap)
|--------------------------------------------------------------------------
*/

Route::post('/webhooks/libelula', LibelulaWebhookController::class)
    ->middleware('throttle:wayna-webhooks')
    ->name('webhooks.libelula');
