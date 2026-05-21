<?php

use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$email = 'emprendedor@wayna.test';
$password = 'password';

if (User::query()->where('email', $email)->exists()) {
    $user = User::query()->where('email', $email)->first();
    $emp = Emprendedor::query()->where('user_id', $user->id)->first();
    echo "Ya existe.\n";
    echo "Correo: {$email}\n";
    echo "Contraseña: {$password}\n";
    echo 'Emprendedor ID: '.($emp?->id ?? 'sin vincular')."\n";
    exit(0);
}

$rol = Rol::query()->firstOrCreate(['nombre' => 'emprendedor']);

$emprendedor = Emprendedor::query()->create([
    'nombre' => 'María',
    'apellidos' => 'Prueba WAYNA',
    'descripcion' => 'Emprendedora de prueba para ingresar al panel y gestionar metas.',
    'tipo_emprendimiento' => 'artesania',
    'departamento' => 'la_paz',
    'estado' => 'activo',
    'meta_monto' => 5000,
]);

$user = User::query()->create([
    'name' => 'María Prueba WAYNA',
    'email' => $email,
    'password' => Hash::make($password),
    'email_verified_at' => now(),
]);

UserRol::query()->firstOrCreate([
    'user_id' => $user->id,
    'role_id' => $rol->id,
]);

$emprendedor->update(['user_id' => $user->id]);

echo "Listo.\n";
echo "Correo: {$email}\n";
echo "Contraseña: {$password}\n";
echo "Login: /login\n";
echo "Mis metas: /emprendedor/mis-metas\n";
echo "Emprendedor ID: {$emprendedor->id}\n";
