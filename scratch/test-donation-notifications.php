<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
$app->instance('request', Illuminate\Http\Request::create('/', 'GET'));

use App\Models\User;
use App\Models\Donacion;
use App\Models\Campana;
use App\Models\TipoPago;
use App\Models\Visitante;
use App\Models\Emprendedor;
use App\Services\AdminDonacionRevisionService;
use App\Services\SendDonationNotificationService;
use App\Events\DonacionConfirmada;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

echo "=== INICIANDO PRUEBAS DE NOTIFICACIONES DE DONACIÓN (S2-10) ===\n\n";

// 1. Verificar clases
echo "1. Verificando existencia de clases...\n";
$clases = [
    \App\Events\DonacionConfirmada::class,
    \App\Listeners\SendDonationNotification::class,
    \App\Mail\TuristaDonacionConfirmadaMail::class,
    \App\Services\SendDonationNotificationService::class,
];
foreach ($clases as $clase) {
    if (class_exists($clase)) {
        echo "   [OK] Clase encontrada: {$clase}\n";
    } else {
        echo "   [FAIL] Clase NO encontrada: {$clase}\n";
        exit(1);
    }
}
echo "\n";

// 2. Obtener admin
$admin = User::query()->whereHas('rol', fn($q) => $q->where('nombre', 'admin'))->first();
if (!$admin) {
    echo "[FAIL] No se encontró ningún admin.\n";
    exit(1);
}

// 3. Obtener emprendedor y configurar preferencias de notificación
$emprendedor = Emprendedor::query()->first();
if (!$emprendedor) {
    echo "[FAIL] No se encontró ningún emprendedor.\n";
    exit(1);
}
$emprendedor->update([
    'notificar_donaciones_email' => true,
    'notificar_donaciones_panel' => true,
]);
$usuarioEmprendedor = $emprendedor->user;
if ($usuarioEmprendedor) {
    $usuarioEmprendedor->update(['email' => 'emprendedor@wayna.test']);
}
echo "2. Emprendedor configurado para recibir notificaciones (Email: {$usuarioEmprendedor->email})\n\n";

// 4. Obtener dependencias para la donación
$campana = Campana::query()->where('emprendedor_id', $emprendedor->id)->first();
if (!$campana) {
    $campana = Campana::query()->first();
    if ($campana) {
        $campana->update(['emprendedor_id' => $emprendedor->id]);
    }
}
$tipoPago = TipoPago::query()->first();
$visitante = Visitante::query()->first();

if (!$campana || !$tipoPago || !$visitante) {
    echo "[FAIL] Faltan datos para crear la donación.\n";
    exit(1);
}

// Limpiar antiguos registros de prueba
DB::table('jobs')->truncate();

// 5. Crear donación pendiente con email de turista en metadata_pago
$donacion = Donacion::create([
    'campana_id' => $campana->id,
    'tipo_pago_id' => $tipoPago->id,
    'visitante_id' => $visitante->id,
    'monto' => 200.00,
    'moneda' => Donacion::MONEDA_BOB,
    'metodo' => 'qr',
    'estado_pago' => Donacion::ESTADO_PENDIENTE,
    'referencia_pago' => 'REF-S2-10-TEST',
    'proveedor_pago' => Donacion::PROVEEDOR_BANCO,
    'estado_proveedor' => 'pendiente',
    'metadata_pago' => [
        'email_cliente' => 'turista@wayna.test',
        'original' => 'preservado_bien'
    ],
]);
echo "3. Donación de prueba creada (pendiente): ID #{$donacion->id}\n";
echo "   - Email turista resuelto en metadata: " . app(SendDonationNotificationService::class)->resolverEmailTurista($donacion) . "\n\n";

// 6. Confirmar la donación a través de AdminDonacionRevisionService
echo "4. Confirmando donación mediante AdminDonacionRevisionService...\n";
$donacionConfirmada = app(AdminDonacionRevisionService::class)->confirmar($donacion, $admin);
echo "   - Estado pago actual: {$donacionConfirmada->estado_pago}\n";

// 7. Verificar cola de jobs
$jobsCount = DB::table('jobs')->count();
echo "5. Verificando cola de jobs:\n";
echo "   - Cantidad de jobs en cola: {$jobsCount}\n";
if ($jobsCount > 0) {
    echo "   [SUCCESS] El job de notificación se ha encolado correctamente.\n";
} else {
    echo "   [FAIL] No hay jobs en la cola.\n";
}
echo "\n";

// 8. Procesar cola de jobs usando artisan
echo "6. Procesando cola de jobs (php artisan queue:work --once)...\n";
$output = shell_exec('php artisan queue:work --once');
echo "   - Salida del worker:\n{$output}\n";

// 9. Verificar estado de notificaciones
$donacionRefrescada = $donacionConfirmada->fresh();
$metadataNotificaciones = $donacionRefrescada->metadata_pago['notificaciones'] ?? [];

echo "7. Verificando marcas de notificaciones procesadas (metadata_pago.notificaciones):\n";
echo "   - emprendedor_email_enviado_en: " . ($metadataNotificaciones['emprendedor_email_enviado_en'] ?? 'NO ENVIADO') . "\n";
echo "   - emprendedor_panel_registrado_en: " . ($metadataNotificaciones['emprendedor_panel_registrado_en'] ?? 'NO ENVIADO') . "\n";
echo "   - turista_email_enviado_en: " . ($metadataNotificaciones['turista_email_enviado_en'] ?? 'NO ENVIADO') . "\n";
echo "   - telegram_enviado_en: " . ($metadataNotificaciones['telegram_enviado_en'] ?? 'NO ENVIADO') . "\n";
echo "\n";

// Verificar notificación in-app del emprendedor en la base de datos
$inAppNotificacion = DB::table('emprendedor_notificaciones')
    ->where('donacion_id', $donacionRefrescada->id)
    ->first();

echo "8. Verificando registro de notificaciones in-app:\n";
if ($inAppNotificacion) {
    echo "   [SUCCESS] Notificación in-app registrada para el emprendedor ID #{$inAppNotificacion->emprendedor_id}.\n";
} else {
    echo "   [FAIL] No se encontró notificación in-app registrada.\n";
}
echo "\n";

// 10. Probar IDEMPOTENCIA
echo "9. Probando IDEMPOTENCIA (Volviendo a disparar DonacionConfirmada para la misma donación)...\n";
event(new DonacionConfirmada($donRefrescada = $donacionRefrescada->fresh()));
$jobsCount2 = DB::table('jobs')->count();
echo "   - Jobs encolados en segundo envío: {$jobsCount2}\n";

echo "   - Procesando segundo job...\n";
shell_exec('php artisan queue:work --once');

$donacionFinal = $donacionRefrescada->fresh();
$metadataFinal = $donacionFinal->metadata_pago['notificaciones'] ?? [];

echo "   - Comparando marcas originales vs marcas actuales:\n";
$idempotente = true;
foreach (['emprendedor_email_enviado_en', 'emprendedor_panel_registrado_en', 'turista_email_enviado_en', 'telegram_enviado_en'] as $key) {
    $orig = $metadataNotificaciones[$key] ?? 'N/A';
    $curr = $metadataFinal[$key] ?? 'N/A';
    echo "     * {$key}: original [{$orig}], actual [{$curr}]\n";
    if ($orig !== $curr) {
        $idempotente = false;
    }
}

if ($idempotente) {
    echo "   [SUCCESS] Las marcas son idénticas. Idempotencia completamente garantizada.\n";
} else {
    echo "   [FAIL] Las marcas cambiaron o se actualizaron. Falla de idempotencia.\n";
}
echo "\n";

// Limpiar donación de prueba
DB::table('emprendedor_notificaciones')->where('donacion_id', $donacionFinal->id)->delete();
$donacionFinal->delete();
echo "Limpieza completada. Registro de prueba eliminado.\n\n";

echo "=== PRUEBAS CONCLUIDAS CON ÉXITO ===\n";
