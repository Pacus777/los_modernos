<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class RoleUserTestSeeder extends Seeder
{
    /**
     * Ejecuta el seeder de prueba para roles y usuarios.
     *
     * Este seeder crea:
     * - Un usuario admin con rol admin.
     * - Un usuario cajero con rol cajero.
     * - Un usuario sin rol para probar el bloqueo del login.
     *
     * Las credenciales se leen desde config/seeding.php (variables SEED_* en .env).
     * Documentación de ejemplo: .env.example
     */
    public function run(): void
    {
        $this->assertSeedingPasswordsAreConfigured();

        $adminRole = Rol::updateOrCreate(
            ['nombre' => 'admin'],
            ['nombre' => 'admin']
        );

        $cajeroRole = Rol::updateOrCreate(
            ['nombre' => 'cajero'],
            ['nombre' => 'cajero']
        );

        Rol::updateOrCreate(
            ['nombre' => 'emprendedor'],
            ['nombre' => 'emprendedor']
        );

        $admin = config('seeding.admin');
        $adminUser = User::updateOrCreate(
            ['email' => $admin['email']],
            [
                'name' => $admin['name'],
                'password' => Hash::make($admin['password']),
                'email_verified_at' => now(),
            ]
        );

        UserRol::updateOrCreate(
            ['user_id' => $adminUser->id],
            ['role_id' => $adminRole->id]
        );

        $cajero = config('seeding.cajero');
        $cajeroUser = User::updateOrCreate(
            ['email' => $cajero['email']],
            [
                'name' => $cajero['name'],
                'password' => Hash::make($cajero['password']),
                'email_verified_at' => now(),
            ]
        );

        UserRol::updateOrCreate(
            ['user_id' => $cajeroUser->id],
            ['role_id' => $cajeroRole->id]
        );

        $sinRol = config('seeding.sin_rol');
        User::updateOrCreate(
            ['email' => $sinRol['email']],
            [
                'name' => $sinRol['name'],
                'password' => Hash::make($sinRol['password']),
                'email_verified_at' => now(),
            ]
        );
    }

    private function assertSeedingPasswordsAreConfigured(): void
    {
        $bloques = [
            'admin' => 'SEED_ADMIN_PASSWORD',
            'cajero' => 'SEED_CAJERO_PASSWORD',
            'sin_rol' => 'SEED_SIN_ROL_PASSWORD',
        ];

        foreach ($bloques as $clave => $variableEntorno) {
            $password = config("seeding.{$clave}.password");

            if (! is_string($password) || $password === '') {
                throw new RuntimeException(
                    "Configura {$variableEntorno} en tu archivo .env (valores de ejemplo en .env.example). ".
                    'Los seeders de usuarios de prueba no pueden ejecutarse sin contraseñas definidas por entorno.'
                );
            }
        }
    }
}
