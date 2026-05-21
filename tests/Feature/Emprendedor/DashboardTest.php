<?php

namespace Tests\Feature\Emprendedor;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\TipoPago;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_emprendedor_accede_al_dashboard(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $this->vincularEmprendedor($user);

        $response = $this->actingAs($user)->get(route('emprendedor.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Emprendedor/Dashboard')
            ->where('panel.perfil.nombre_completo', 'Ana Torres')
            ->has('panel.estadisticas')
            ->has('panel.progreso'));
    }

    public function test_ruta_panel_redirige_a_dashboard(): void
    {
        $user = $this->crearUsuarioEmprendedor();

        $this->actingAs($user)
            ->get('/emprendedor/panel')
            ->assertRedirect('/emprendedor/dashboard');
    }

    public function test_dashboard_muestra_estadisticas_de_donaciones(): void
    {
        $user = $this->crearUsuarioEmprendedor();
        $emprendedor = $this->vincularEmprendedor($user);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Meta verano',
            'meta_apoyo' => 1000,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addMonth(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoPago = TipoPago::query()->create([
            'nombre' => 'Efectivo',
            'codigo' => 'efectivo',
            'activo' => true,
            'requiere_validacion_manual' => true,
        ]);

        Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 150,
            'moneda' => Donacion::MONEDA_BOB,
            'metodo' => 'efectivo',
            'estado_pago' => Donacion::ESTADO_VALIDADO,
            'proveedor_pago' => Donacion::PROVEEDOR_MANUAL,
            'referencia_pago' => 'REF-VALIDADA',
        ]);

        Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 50,
            'moneda' => Donacion::MONEDA_BOB,
            'metodo' => 'efectivo',
            'estado_pago' => Donacion::ESTADO_PENDIENTE,
            'proveedor_pago' => Donacion::PROVEEDOR_MANUAL,
            'referencia_pago' => 'REF-PENDIENTE',
        ]);

        $this->actingAs($user)
            ->get(route('emprendedor.dashboard'))
            ->assertInertia(fn ($page) => $page
                ->where('panel.estadisticas.donaciones_validadas_total', 150)
                ->where('panel.estadisticas.donaciones_validadas_cantidad', 1)
                ->where('panel.estadisticas.donaciones_pendientes_cantidad', 1)
                ->where('panel.campana_activa.titulo', 'Meta verano'));
    }

    public function test_turista_no_puede_acceder_al_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('emprendedor.dashboard'))
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
