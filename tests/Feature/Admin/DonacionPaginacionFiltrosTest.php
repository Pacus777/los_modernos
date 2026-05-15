<?php

namespace Tests\Feature\Admin;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\TipoPago;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonacionPaginacionFiltrosTest extends TestCase
{
    use RefreshDatabase;

    public function test_listado_paginado_conserva_filtros_en_links(): void
    {
        $admin = $this->crearUsuarioAdmin();
        [$emprendedorA, $emprendedorB, $campanaA, $tipoPago] = $this->crearEscenarioDosEmprendedores();

        for ($i = 0; $i < 14; $i++) {
            $this->crearDonacion($campanaA, $tipoPago, 50 + $i, Donacion::ESTADO_VALIDADO);
        }

        $campanaB = Campana::query()->create([
            'emprendedor_id' => $emprendedorB->id,
            'titulo' => 'Campaña B',
            'meta_apoyo' => 1000,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $this->crearDonacion($campanaB, $tipoPago, 200, Donacion::ESTADO_VALIDADO);

        $response = $this->actingAs($admin)->get(route('admin.donaciones.index', [
            'emprendedor_id' => $emprendedorA->id,
            'estado_pago' => Donacion::ESTADO_VALIDADO,
        ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Donaciones/Index')
            ->where('filters.emprendedor_id', (string) $emprendedorA->id)
            ->where('filters.estado_pago', Donacion::ESTADO_VALIDADO)
            ->where('donaciones.per_page', 12)
            ->where('donaciones.total', 14)
            ->where('donaciones.last_page', 2)
            ->has('donaciones.data', 12)
            ->has('donaciones.links', 4)
        );

        $page2 = $this->actingAs($admin)->get(route('admin.donaciones.index', [
            'emprendedor_id' => $emprendedorA->id,
            'estado_pago' => Donacion::ESTADO_VALIDADO,
            'page' => 2,
        ]));

        $page2->assertOk();
        $page2->assertInertia(fn ($page) => $page
            ->has('donaciones.data', 2)
            ->where('donaciones.current_page', 2)
        );
    }

    public function test_filtro_por_emprendedor_en_pagina_dos(): void
    {
        $admin = $this->crearUsuarioAdmin();
        [$emprendedorA, , $campanaA, $tipoPago] = $this->crearEscenarioDosEmprendedores();

        for ($i = 0; $i < 15; $i++) {
            $this->crearDonacion($campanaA, $tipoPago, 10, Donacion::ESTADO_PENDIENTE);
        }

        $response = $this->actingAs($admin)->get(route('admin.donaciones.index', [
            'emprendedor_id' => $emprendedorA->id,
            'page' => 2,
        ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('donaciones.data', 3)
            ->where('donaciones.current_page', 2)
            ->where('filters.emprendedor_id', (string) $emprendedorA->id)
        );
    }

    private function crearEscenarioDosEmprendedores(): array
    {
        $emprendedorA = Emprendedor::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Uno',
            'descripcion' => 'Emprendimiento de prueba A',
            'estado' => 'activo',
            'meta_monto' => 1000,
        ]);

        $emprendedorB = Emprendedor::query()->create([
            'nombre' => 'Luis',
            'apellidos' => 'Dos',
            'descripcion' => 'Emprendimiento de prueba B',
            'estado' => 'activo',
            'meta_monto' => 1000,
        ]);

        $campanaA = Campana::query()->create([
            'emprendedor_id' => $emprendedorA->id,
            'titulo' => 'Campaña A',
            'meta_apoyo' => 1000,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoPago = TipoPago::query()->create([
            'codigo' => 'efectivo_test',
            'nombre' => 'Efectivo',
            'descripcion' => null,
            'activo' => true,
        ]);

        return [$emprendedorA, $emprendedorB, $campanaA, $tipoPago];
    }

    private function crearDonacion(Campana $campana, TipoPago $tipoPago, float $monto, string $estado): Donacion
    {
        return Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'visitante_id' => null,
            'monto' => $monto,
            'metodo' => 'efectivo',
            'estado_pago' => $estado,
            'referencia_pago' => 'REF-'.uniqid(),
        ]);
    }

    private function crearUsuarioAdmin(): User
    {
        $rol = Rol::query()->create(['nombre' => 'admin']);
        $user = User::factory()->create();

        UserRol::query()->create([
            'user_id' => $user->id,
            'role_id' => $rol->id,
        ]);

        return $user->fresh();
    }
}
