<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rol;
use App\Models\UserRol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
     * Importante:
     * Este seeder es solo para desarrollo y pruebas locales.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Crear roles base del sistema
        |--------------------------------------------------------------------------
        |
        | Los roles iniciales del sistema WAYNA son:
        | - admin: usuario con acceso al panel administrativo.
        | - cajero: usuario limitado a la pantalla de pagos en efectivo.
        |
        | Usamos updateOrCreate para evitar duplicados si el seeder se ejecuta
        | varias veces durante el desarrollo.
        |
        */

        $adminRole = Rol::updateOrCreate(
            ['nombre' => 'admin'],
            ['nombre' => 'admin']
        );

        $cajeroRole = Rol::updateOrCreate(
            ['nombre' => 'cajero'],
            ['nombre' => 'cajero']
        );

        /*
        |--------------------------------------------------------------------------
        | 2. Crear usuario administrador
        |--------------------------------------------------------------------------
        |
        | Este usuario debe entrar al panel general del administrador.
        | Sirve para probar la redirección hacia admin.dashboard.
        |
        */

        $adminUser = User::updateOrCreate(
            ['email' => 'admin@wayna.test'],
            [
                'name' => 'Administrador WAYNA',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 3. Asignar rol admin al usuario administrador
        |--------------------------------------------------------------------------
        |
        | La tabla user_roles tiene unique(user_id), por eso cada usuario
        | solo puede tener un rol asignado.
        |
        | updateOrCreate garantiza que si el usuario ya tenía una asignación,
        | se actualice en vez de crear otra fila duplicada.
        |
        */

        UserRol::updateOrCreate(
            ['user_id' => $adminUser->id],
            ['role_id' => $adminRole->id]
        );

        /*
        |--------------------------------------------------------------------------
        | 4. Crear usuario cajero
        |--------------------------------------------------------------------------
        |
        | Este usuario debe entrar directamente a la pantalla de efectivo.
        | Sirve para probar la redirección hacia cajero.efectivo.
        |
        */

        $cajeroUser = User::updateOrCreate(
            ['email' => 'cajero@wayna.test'],
            [
                'name' => 'Cajero WAYNA',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 5. Asignar rol cajero al usuario cajero
        |--------------------------------------------------------------------------
        |
        | Este rol debe permitir únicamente las funciones relacionadas
        | con validación o confirmación de pagos en efectivo.
        |
        */

        UserRol::updateOrCreate(
            ['user_id' => $cajeroUser->id],
            ['role_id' => $cajeroRole->id]
        );

        /*
        |--------------------------------------------------------------------------
        | 6. Crear usuario sin rol
        |--------------------------------------------------------------------------
        |
        | Este usuario NO se registra en user_roles.
        | Sirve para probar que el sistema lo rechace después del login
        | y lo devuelva a la pantalla de acceso con un mensaje de error.
        |
        */

        User::updateOrCreate(
            ['email' => 'sinrol@wayna.test'],
            [
                'name' => 'Usuario Sin Rol',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}