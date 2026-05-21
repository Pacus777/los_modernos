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
use App\Services\AdminDonacionRevisionService;
use Illuminate\Validation\ValidationException;

echo "=== INICIANDO PRUEBAS DE REVISIÓN DE DONACIONES (S2-08) ===\n\n";

// 1. Obtener un usuario administrador
$admin = User::query()->whereHas('rol', fn($q) => $q->where('nombre', 'admin'))->first();
if (!$admin) {
    echo "[FAIL] No se encontró ningún usuario administrador en la BD.\n";
    exit(1);
}
echo "[OK] Administrador encontrado: {$admin->email}\n";

// 2. Obtener dependencias para crear donaciones de prueba
$campana = Campana::query()->first();
$tipoPago = TipoPago::query()->first();
$visitante = Visitante::query()->first();

if (!$campana || !$tipoPago || !$visitante) {
    echo "[FAIL] Faltan datos semilla (Campana, TipoPago, Visitante) para la prueba.\n";
    exit(1);
}

// 3. Crear donación 1 (Prueba de confirmación y doble confirmación)
$donacion1 = Donacion::create([
    'campana_id' => $campana->id,
    'tipo_pago_id' => $tipoPago->id,
    'visitante_id' => $visitante->id,
    'monto' => 150.00,
    'moneda' => Donacion::MONEDA_BOB,
    'metodo' => 'qr',
    'estado_pago' => Donacion::ESTADO_PENDIENTE,
    'referencia_pago' => 'REF-TEST-CONFIRM',
    'proveedor_pago' => Donacion::PROVEEDOR_BANCO,
    'estado_proveedor' => 'pendiente',
    'metadata_pago' => ['original' => 'test_data_preserved'],
]);
echo "[OK] Donación de prueba 1 creada (pendiente): ID #{$donacion1->id}\n";

// 4. Crear donación 2 (Prueba de rechazo)
$donacion2 = Donacion::create([
    'campana_id' => $campana->id,
    'tipo_pago_id' => $tipoPago->id,
    'visitante_id' => $visitante->id,
    'monto' => 300.00,
    'moneda' => Donacion::MONEDA_BOB,
    'metodo' => 'qr',
    'estado_pago' => Donacion::ESTADO_PENDIENTE,
    'referencia_pago' => 'REF-TEST-REJECT',
    'proveedor_pago' => Donacion::PROVEEDOR_BANCO,
    'estado_proveedor' => 'pendiente',
    'metadata_pago' => ['original' => 'test_data_preserved'],
]);
echo "[OK] Donación de prueba 2 creada (pendiente): ID #{$donacion2->id}\n\n";

$service = app(AdminDonacionRevisionService::class);

// --- PRUEBA 1: CONFIRMAR DONACIÓN 1 ---
echo "--- PRUEBA 1: Confirmando Donación 1 ---\n";
try {
    $donacion1Confirmada = $service->confirmar($donacion1, $admin);
    
    if ($donacion1Confirmada->estado_pago === Donacion::ESTADO_VALIDADO &&
        $donacion1Confirmada->estado_proveedor === 'validado_admin' &&
        $donacion1Confirmada->pagado_en !== null &&
        isset($donacion1Confirmada->metadata_pago['revision_admin']) &&
        $donacion1Confirmada->metadata_pago['revision_admin']['accion'] === 'confirmada' &&
        $donacion1Confirmada->metadata_pago['original'] === 'test_data_preserved'
    ) {
        echo "[SUCCESS] Donación 1 confirmada exitosamente con todas las reglas de negocio:\n";
        echo "          - Estado: {$donacion1Confirmada->estado_pago}\n";
        echo "          - Estado proveedor: {$donacion1Confirmada->estado_proveedor}\n";
        echo "          - Pagado en: {$donacion1Confirmada->pagado_en}\n";
        echo "          - Metadata original preservada: SÍ\n";
        echo "          - Metadata de revisión admin: " . json_encode($donacion1Confirmada->metadata_pago['revision_admin']) . "\n";
    } else {
        echo "[FAIL] Donación 1 no cumple con las reglas esperadas tras confirmar.\n";
    }
} catch (Exception $e) {
    echo "[FAIL] Error al confirmar Donación 1: " . $e->getMessage() . "\n";
}

// --- PRUEBA 2: EVITAR DOBLE CONFIRMACIÓN ---
echo "\n--- PRUEBA 2: Intentando Confirmar de nuevo la Donación 1 ---\n";
try {
    $service->confirmar($donacion1Confirmada, $admin);
    echo "[FAIL] Se permitió una doble confirmación de una donación ya validada.\n";
} catch (ValidationException $e) {
    echo "[SUCCESS] Doble confirmación bloqueada correctamente. Mensaje: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "[FAIL] Ocurrió un error inesperado al intentar doble confirmar: " . $e->getMessage() . "\n";
}

// --- PRUEBA 3: RECHAZAR DONACIÓN 2 ---
echo "\n--- PRUEBA 3: Rechazando Donación 2 con motivo ---\n";
try {
    $motivo = "Comprobante de depósito falso o ilegible.";
    $donacion2Rechazada = $service->rechazar($donacion2, $admin, $motivo);
    
    if ($donacion2Rechazada->estado_pago === Donacion::ESTADO_RECHAZADO &&
        $donacion2Rechazada->estado_proveedor === 'rechazado_admin' &&
        $donacion2Rechazada->pagado_en === null &&
        isset($donacion2Rechazada->metadata_pago['revision_admin']) &&
        $donacion2Rechazada->metadata_pago['revision_admin']['accion'] === 'rechazada' &&
        $donacion2Rechazada->metadata_pago['revision_admin']['motivo'] === $motivo &&
        $donacion2Rechazada->metadata_pago['original'] === 'test_data_preserved'
    ) {
        echo "[SUCCESS] Donación 2 rechazada exitosamente con todas las reglas de negocio:\n";
        echo "          - Estado: {$donacion2Rechazada->estado_pago}\n";
        echo "          - Estado proveedor: {$donacion2Rechazada->estado_proveedor}\n";
        echo "          - Pagado en: " . ($donacion2Rechazada->pagado_en ?? 'NULL') . "\n";
        echo "          - Metadata de revisión admin: " . json_encode($donacion2Rechazada->metadata_pago['revision_admin']) . "\n";
    } else {
        echo "[FAIL] Donación 2 no cumple con las reglas esperadas tras rechazar.\n";
    }
} catch (Exception $e) {
    echo "[FAIL] Error al rechazar Donación 2: " . $e->getMessage() . "\n";
}

// --- PRUEBA 4: EVITAR DOBLE RECHAZO / CONFIRMAR RECHAZADA ---
echo "\n--- PRUEBA 4: Intentando Confirmar la Donación 2 (que ya fue rechazada) ---\n";
try {
    $service->confirmar($donacion2Rechazada, $admin);
    echo "[FAIL] Se permitió confirmar una donación que ya está rechazada.\n";
} catch (ValidationException $e) {
    echo "[SUCCESS] Acción sobre donación no pendiente bloqueada correctamente. Mensaje: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "[FAIL] Ocurrió un error inesperado al intentar confirmar rechazada: " . $e->getMessage() . "\n";
}

// --- LIMPIEZA ---
echo "\nLimpiando donaciones de prueba...\n";
$donacion1->delete();
$donacion2->delete();
echo "[OK] Registros de prueba eliminados.\n\n";

echo "=== PRUEBAS CONCLUIDAS CON ÉXITO ===\n";
