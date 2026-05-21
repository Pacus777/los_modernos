<?php

namespace Tests\Feature\Emprendedor;

use App\Enums\AuditAction;
use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerfilEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_emprendedor_ve_formulario_de_edicion(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $this->vincularEmprendedor($user);

        $this->actingAs($user)
            ->get(route('emprendedor.perfil.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Emprendedor/PerfilEdit')
                ->where('emprendedor.nombre', 'Ana'));
    }

    public function test_emprendedor_actualiza_perfil_publico(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $emprendedor = $this->vincularEmprendedor($user);

        $response = $this->actingAs($user)->put(route('emprendedor.perfil.update'), [
            'nombre' => 'María',
            'apellidos' => 'López',
            'descripcion' => 'Nueva descripción del emprendimiento con más de diez caracteres.',
            'tipo_emprendimiento' => 'gastronomia',
            'departamento' => 'cochabamba',
            'whatsapp' => '71234567',
            'instagram' => 'https://instagram.com/maria',
        ]);

        $response->assertRedirect(route('emprendedor.dashboard'));
        $response->assertSessionHas('success');

        $emprendedor->refresh();
        $this->assertSame('María', $emprendedor->nombre);
        $this->assertSame('López', $emprendedor->apellidos);
        $this->assertSame('gastronomia', $emprendedor->tipo_emprendimiento->value);
        $this->assertSame('59171234567', $emprendedor->whatsapp);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::EmprendedorProfileUpdated->value,
            'user_id' => $user->id,
        ]);
    }

    public function test_emprendedor_no_puede_cambiar_estado_ni_meta(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $emprendedor = $this->vincularEmprendedor($user);

        $this->actingAs($user)->put(route('emprendedor.perfil.update'), [
            'nombre' => 'Ana',
            'apellidos' => 'Torres',
            'descripcion' => 'Descripción de prueba con más de diez caracteres.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'inactivo',
            'meta_monto' => 99999,
        ]);

        $emprendedor->refresh();
        $this->assertSame('activo', $emprendedor->estado);
        $this->assertSame('500.00', $emprendedor->meta_monto);
    }

    public function test_turista_no_accede_a_edicion_perfil(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('emprendedor.perfil.edit'))
            ->assertForbidden();
    }

    private function crearUsuarioEmprendedor(): User
    {
        $rol = Rol::query()->firstOrCreate(['nombre' => 'emprendedor']);
        $user = User::factory()->create();
        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => $rol->id,
        ]);

        return $user;
    }

    private function vincularEmprendedor(User $user): Emprendedor
    {
        return Emprendedor::query()->create([
            'user_id' => $user->id,
            'nombre' => 'Ana',
            'apellidos' => 'Torres',
            'descripcion' => 'Descripción de prueba con más de diez caracteres.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 500,
            'fotografia' => 'fotografias/test.jpg',
        ]);
    }
}
