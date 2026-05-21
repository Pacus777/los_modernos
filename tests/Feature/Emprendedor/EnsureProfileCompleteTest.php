<?php

namespace Tests\Feature\Emprendedor;

use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureProfileCompleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_emprendedor_con_perfil_incompleto_redirige_a_editar_perfil(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $this->crearPerfilIncompleto($user);

        $response = $this->actingAs($user)->get(route('emprendedor.dashboard'));

        $response->assertRedirect(route('emprendedor.perfil.edit'));
        $response->assertSessionHas('error');
    }

    public function test_emprendedor_con_perfil_incompleto_puede_ver_formulario_de_edicion(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $this->crearPerfilIncompleto($user);

        $response = $this->actingAs($user)->get(route('emprendedor.perfil.edit'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Emprendedor/PerfilEdit')
            ->has('emprendedorOnboarding')
            ->where('emprendedorOnboarding.completo', false)
        );
    }

    public function test_emprendedor_con_perfil_incompleto_puede_actualizar_perfil(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $emprendedor = $this->crearPerfilIncompleto($user);

        $response = $this->actingAs($user)->put(route('emprendedor.perfil.update'), [
            'nombre' => 'Ana',
            'apellidos' => 'Torres',
            'descripcion' => 'Descripción de prueba con más de diez caracteres.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
        ]);

        $response->assertRedirect(route('emprendedor.dashboard'));
        $emprendedor->refresh();
        $this->assertSame('Ana', $emprendedor->nombre);
    }

    public function test_emprendedor_con_perfil_completo_accede_directamente_al_dashboard(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $this->crearPerfilCompleto($user);

        $response = $this->actingAs($user)->get(route('emprendedor.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Emprendedor/Dashboard')
            ->has('emprendedorOnboarding')
            ->where('emprendedorOnboarding.completo', true)
        );
    }

    public function test_rutas_de_password_obligatorio_no_se_bloquean_por_perfil_incompleto(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $user->update(['must_change_password' => true]);
        $this->crearPerfilIncompleto($user);

        $response = $this->actingAs($user)->get(route('emprendedor.password.force'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Emprendedor/CambiarPassword'));
    }

    public function test_usuario_no_emprendedor_no_es_afectado_por_onboarding(): void
    {
        // Turista común sin rol de emprendedor
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($user)->get(route('emprendedor.dashboard'));

        // Debe ser bloqueado por el middleware de rol, no redirigido a editar perfil
        $response->assertForbidden();
    }

    private function crearUsuarioEmprendedor(): User
    {
        $rol = Rol::query()->firstOrCreate(['nombre' => 'emprendedor']);
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'must_change_password' => false,
        ]);
        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => $rol->id,
        ]);

        return $user;
    }

    private function crearPerfilIncompleto(User $user): Emprendedor
    {
        return Emprendedor::query()->create([
            'user_id' => $user->id,
            'nombre' => '',
            'apellidos' => '',
            'descripcion' => '',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'fotografia' => null,
            'estado' => 'activo',
        ]);
    }

    private function crearPerfilCompleto(User $user): Emprendedor
    {
        return Emprendedor::query()->create([
            'user_id' => $user->id,
            'nombre' => 'Juan Carlos',
            'apellidos' => 'Pérez Quispe',
            'descripcion' => 'Descripción de prueba con más de 10 caracteres.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'fotografia' => 'fotografias/perfil_test.jpg',
            'estado' => 'activo',
        ]);
    }
}
