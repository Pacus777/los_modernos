<?php

use App\Models\Campana;
use App\Models\Emprendedor;
use App\Models\EmprendedorPost;
use App\Models\EmprendedorSeguidor;
use App\Models\TuristaNotificacion;
use App\Models\User;
use App\Models\Rol;
use App\Models\UserRol;
use App\Services\TuristaNotificacionService;
use App\Jobs\NuevoPostEmail;
use App\Jobs\NuevaMetaEmail;
use App\Enums\EmprendedorPostEstado;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== INICIANDO PRUEBAS DE NOTIFICACIONES TURISTA ===\n";

// 1. Limpiar base de datos de pruebas previas
echo "Limpiando datos previos...\n";
User::query()->where('email', 'turista_test@wayna.test')->delete();
User::query()->where('email', 'emprendedor_test@wayna.test')->delete();
Emprendedor::query()->where('descripcion', 'Emprendedor de prueba para notificaciones')->delete();
\App\Models\Visitante::query()->where('codigo', 'VIS-TEST-NOTIF')->delete();

// 2. Crear Emprendedor y su cuenta
echo "Creando Emprendedor y cuenta...\n";
$rolEmp = Rol::query()->firstOrCreate(['nombre' => 'emprendedor']);
$emprendedor = Emprendedor::query()->create([
    'nombre' => 'Julio',
    'apellidos' => 'Seguidor Test',
    'descripcion' => 'Emprendedor de prueba para notificaciones',
    'tipo_emprendimiento' => 'artesania',
    'departamento' => 'la_paz',
    'estado' => 'activo',
    'meta_monto' => 5000,
]);
$userEmp = User::query()->create([
    'name' => 'Julio Emprendedor Test',
    'email' => 'emprendedor_test@wayna.test',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
]);
UserRol::query()->create([
    'user_id' => $userEmp->id,
    'role_id' => $rolEmp->id,
]);
$emprendedor->update(['user_id' => $userEmp->id]);

// 3. Crear Turista con Cuenta (usuario sin rol)
echo "Creando Turista con cuenta...\n";
$userTurista = User::query()->create([
    'name' => 'Ana Turista Test',
    'email' => 'turista_test@wayna.test',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
]);

// Verificar que sea turista cuenta
if ($userTurista->esTuristaCuenta()) {
    echo "[OK] Ana es identificada como turista con cuenta.\n";
} else {
    echo "[ERROR] Ana NO es identificada como turista con cuenta.\n";
    exit(1);
}

// 4. Crear Seguimiento
echo "Vinculando Turista como seguidor de Julio...\n";
$visitante = \App\Models\Visitante::query()->create([
    'codigo' => 'VIS-TEST-NOTIF',
    'nombre' => 'Ana Turista Test',
    'idioma' => 'es',
]);

$seguimiento = EmprendedorSeguidor::query()->create([
    'emprendedor_id' => $emprendedor->id,
    'visitante_id' => $visitante->id,
    'email' => 'turista_test@wayna.test',
    'confirmado_en' => now(),
    'token_unsub' => Str::random(40),
]);

// 5. Crear y publicar un post, luego despachar NuevoPostEmail
echo "Creando una nueva publicación (Post) de Julio...\n";
$post = EmprendedorPost::query()->create([
    'emprendedor_id' => $emprendedor->id,
    'tipo' => 'texto',
    'estado' => EmprendedorPostEstado::Publicado,
    'contenido' => 'Hola seguidores, ¡bienvenidos a mi taller virtual!',
    'publicado_en' => now(),
]);

echo "Despachando Job NuevoPostEmail...\n";
NuevoPostEmail::dispatchSync($post);

// Verificar notificación
$notifPost = TuristaNotificacion::query()
    ->where('user_id', $userTurista->id)
    ->where('tipo', TuristaNotificacion::TIPO_NUEVO_POST)
    ->first();

if ($notifPost) {
    echo "[OK] Notificación de nuevo post persistida exitosamente.\n";
    echo "     Título: {$notifPost->titulo}\n";
    echo "     Mensaje: {$notifPost->mensaje}\n";
    echo "     Url: {$notifPost->url}\n";
} else {
    echo "[ERROR] No se persistió la notificación de nuevo post.\n";
    exit(1);
}

// 6. Crear una meta de campaña visible, luego despachar NuevaMetaEmail
echo "Creando una nueva campaña (Meta) de Julio...\n";
$campana = Campana::query()->create([
    'emprendedor_id' => $emprendedor->id,
    'titulo' => 'Comprar un nuevo horno artesanal',
    'meta_apoyo' => 3000,
    'estado' => Campana::ESTADO_ACTIVA,
    'fecha_inicio' => now(),
    'fecha_fin' => now()->addMonth(),
]);

echo "Despachando Job NuevaMetaEmail...\n";
NuevaMetaEmail::dispatchSync($campana);

// Verificar notificación
$notifCampana = TuristaNotificacion::query()
    ->where('user_id', $userTurista->id)
    ->where('tipo', TuristaNotificacion::TIPO_NUEVA_META)
    ->first();

if ($notifCampana) {
    echo "[OK] Notificación de nueva campaña persistida exitosamente.\n";
    echo "     Título: {$notifCampana->titulo}\n";
    echo "     Mensaje: {$notifCampana->mensaje}\n";
    echo "     Url: {$notifCampana->url}\n";
} else {
    echo "[ERROR] No se persistió la notificación de nueva campaña.\n";
    exit(1);
}

// 7. Probar servicios de marcar como leída
echo "Probando marcas de lectura...\n";
$service = app(TuristaNotificacionService::class);

echo "Contador antes de leer: {$service->contarNoLeidas($userTurista)}\n";

$service->marcarLeida($userTurista, $notifPost->id);
if ($notifPost->fresh()->estaLeida()) {
    echo "[OK] Notificación de post marcada como leída individualmente.\n";
} else {
    echo "[ERROR] Falla al marcar leída individual.\n";
    exit(1);
}

echo "Contador después de una lectura: {$service->contarNoLeidas($userTurista)}\n";

$service->marcarTodasLeidas($userTurista);
if ($notifCampana->fresh()->estaLeida()) {
    echo "[OK] Todas las notificaciones marcadas como leídas de forma global.\n";
} else {
    echo "[ERROR] Falla al marcar todas leídas.\n";
    exit(1);
}

echo "Contador final no leídas: {$service->contarNoLeidas($userTurista)}\n";

// 8. Resumen para Navbar
$resumen = $service->resumenParaNavbar($userTurista);
echo "[OK] Resumen para Navbar cargado: " . count($resumen['items']) . " elementos. No leídas: {$resumen['no_leidas']}\n";

// Limpiar base de datos
echo "Limpiando registros de prueba creados...\n";
$notifPost->delete();
$notifCampana->delete();
$post->delete();
$campana->delete();
$seguimiento->delete();
$visitante->delete();
$userTurista->delete();
$emprendedor->delete();
$userEmp->delete();

echo "=== PRUEBAS CONCLUIDAS CON ÉXITO ===\n";
