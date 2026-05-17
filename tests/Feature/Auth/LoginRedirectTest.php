<?php

namespace Tests\Feature\Auth;

use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_ignora_intended_dashboard_generico(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->withSession(['url.intended' => url('/dashboard')])
            ->post('/login', [
                'email' => $admin->email,
                'password' => 'password',
            ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_login_vuelve_a_pagina_admin_prevista(): void
    {
        $admin = $this->crearAdmin();
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Test',
            'apellidos' => 'User',
            'descripcion' => 'Desc',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 100,
        ]);

        $editUrl = route('admin.emprendedores.edit', $emprendedor, absolute: false);

        $response = $this->withSession(['url.intended' => $editUrl])
            ->post('/login', [
                'email' => $admin->email,
                'password' => 'password',
            ]);

        $response->assertRedirect($editUrl);
    }

    public function test_dashboard_redirige_admin_al_panel_wayna(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    private function crearAdmin(): User
    {
        $rol = Rol::query()->create(['nombre' => 'admin']);
        $user = User::factory()->create();
        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => $rol->id,
        ]);

        return $user;
    }
}
