<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
$app->instance('request', Illuminate\Http\Request::create('/', 'GET'));

use App\Models\User;
use App\Services\EmprendedorOnboardingService;

$u = User::query()->whereHas('rol', fn($q) => $q->where('nombre', 'emprendedor'))->first();
if (!$u) {
    echo "No se encontró ningún usuario emprendedor en la BD.\n";
    exit(1);
}

$e = $u->emprendedor;
if (!$e) {
    echo "El usuario emprendedor no tiene un perfil de emprendedor asociado.\n";
    exit(1);
}

echo "=== PROBANDO ONBOARDING DEL EMPRENDEDOR: {$e->nombreCompleto()} ===\n";
$service = app(EmprendedorOnboardingService::class);
$checklist = $service->checklist($e);

echo "Porcentaje completado: {$checklist['porcentaje']}%\n";
echo "Perfil completo (Bloqueantes superados): " . ($checklist['completo'] ? 'SÍ' : 'NO') . "\n\n";

echo "Checklist Items:\n";
foreach ($checklist['items'] as $item) {
    $estado = $item['completo'] ? '[COMPLETO]' : '[PENDIENTE]';
    $tipo = $item['bloqueante'] ? '(Obligatorio)' : '(Recomendado)';
    echo " - {$estado} {$item['titulo']} {$tipo}\n";
    echo "   Detalle: {$item['descripcion']}\n";
}

echo "\n--- SIMULANDO PERFIL INCOMPLETO (sin fotografía) ---\n";
$e->fotografia = null;
$checklistIncompleto = $service->checklist($e);

echo "Porcentaje completado: {$checklistIncompleto['porcentaje']}%\n";
echo "Perfil completo (Bloqueantes superados): " . ($checklistIncompleto['completo'] ? 'SÍ' : 'NO') . "\n\n";

echo "Checklist Items (Simulado):\n";
foreach ($checklistIncompleto['items'] as $item) {
    $estado = $item['completo'] ? '[COMPLETO]' : '[PENDIENTE]';
    $tipo = $item['bloqueante'] ? '(Obligatorio)' : '(Recomendado)';
    echo " - {$estado} {$item['titulo']} {$tipo}\n";
}

