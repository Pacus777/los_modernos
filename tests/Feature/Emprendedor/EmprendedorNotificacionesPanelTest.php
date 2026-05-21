<?php

namespace Tests\Feature\Emprendedor;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\EmprendedorNotificacion;
use App\Models\Rol;
use App\Models\TipoPago;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * E-11 — Campanita y avisos in-app en el panel emprendedor.
 */
class EmprendedorNotificacionesPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_dashboard_comparte_resumen_para_campanita(): void
    {
        [$user, $emprendedor] = $this->escenarioConNotificacion();

        $this->actingAs($user)
            ->get(route('emprendedor.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('emprendedorNotificaciones')
                ->where('emprendedorNotificaciones.no_leidas', 1)
                ->has('emprendedorNotificaciones.items', 1));
    }

    public function test_pagina_mis_avisos_lista_notificaciones(): void
    {
        [$user] = $this->escenarioConNotificacion();

        $this->actingAs($user)
            ->get(route('emprendedor.notificaciones.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Emprendedor/Notificaciones/Index')
                ->where('no_leidas', 1)
                ->has('notificaciones.data', 1));
    }

    public function test_marcar_leida_desde_panel(): void
    {
        [$user, $emprendedor] = $this->escenarioConNotificacion();

        $notificacion = EmprendedorNotificacion::query()
            ->where('emprendedor_id', $emprendedor->id)
            ->firstOrFail();

        $this->actingAs($user)
            ->patch(route('emprendedor.notificaciones.leer', $notificacion->id))
            ->assertRedirect();

        $this->assertNotNull($notificacion->fresh()->leida_at);
    }

    public function test_marcar_todas_leidas(): void
    {
        [$user, $emprendedor] = $this->escenarioConNotificacion();

        $donacion2 = Donacion::query()->create([
            'campana_id' => $emprendedor->campanas()->first()->id,
            'tipo_pago_id' => TipoPago::query()->first()->id,
            'monto' => 50,
            'moneda' => Donacion::MONEDA_BOB,
            'metodo' => 'efectivo',
            'estado_pago' => Donacion::ESTADO_PENDIENTE,
            'proveedor_pago' => Donacion::PROVEEDOR_MANUAL,
            'referencia_pago' => 'REF-campanita-2',
        ]);
        $donacion2->update(['estado_pago' => Donacion::ESTADO_VALIDADO]);

        $this->actingAs($user)
            ->post(route('emprendedor.notificaciones.marcar-todas'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(
            0,
            EmprendedorNotificacion::query()
                ->where('emprendedor_id', $emprendedor->id)
                ->whereNull('leida_at')
                ->count(),
        );
    }

    /**
     * @return array{0: User, 1: Emprendedor}
     */
    private function escenarioConNotificacion(): array
    {
        $rol = Rol::query()->firstOrCreate(['nombre' => 'emprendedor']);
        $user = User::factory()->create();
        UserRol::query()->create(['user_id' => $user->id, 'role_id' => $rol->id]);

        $emprendedor = Emprendedor::query()->create([
            'user_id' => $user->id,
            'nombre' => 'Campana',
            'apellidos' => 'Test',
            'descripcion' => 'Descripción de prueba con más de diez caracteres.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 500,
            'notificar_donaciones_panel' => true,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Meta campanita',
            'meta_apoyo' => 1000,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addMonth(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoPago = TipoPago::query()->create([
            'codigo' => 'efectivo_campanita',
            'nombre' => 'Efectivo',
            'activo' => true,
        ]);

        $donacion = Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 80,
            'moneda' => Donacion::MONEDA_BOB,
            'metodo' => 'efectivo',
            'estado_pago' => Donacion::ESTADO_PENDIENTE,
            'proveedor_pago' => Donacion::PROVEEDOR_MANUAL,
            'referencia_pago' => 'REF-campanita',
        ]);

        $donacion->update(['estado_pago' => Donacion::ESTADO_VALIDADO]);

        return [$user, $emprendedor];
    }
}
