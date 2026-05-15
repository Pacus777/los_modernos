<?php

namespace Tests\Feature\Admin;

use App\Enums\RangoMonto;
use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\Rol;
use App\Models\TipoPago;
use App\Models\User;
use App\Models\UserRol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RangoMontoFiltroTest extends TestCase
{
    use RefreshDatabase;

    public function test_clasifica_rangos_segun_umbrales(): void
    {
        $this->assertSame(RangoMonto::Bajo, RangoMonto::clasificar(500));
        $this->assertSame(RangoMonto::Medio, RangoMonto::clasificar(501));
        $this->assertSame(RangoMonto::Medio, RangoMonto::clasificar(2000));
        $this->assertSame(RangoMonto::Alto, RangoMonto::clasificar(2000.01));
        $this->assertSame(RangoMonto::Alto, RangoMonto::clasificar(5000));
    }

    public function test_filtro_rango_medio_en_campanas_por_meta_apoyo(): void
    {
        $admin = $this->crearUsuarioAdmin();
        $emprendedor = $this->crearEmprendedor();

        Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Meta baja',
            'meta_apoyo' => 300,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_INACTIVA,
        ]);

        $medio = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Meta media',
            'meta_apoyo' => 1500,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_INACTIVA,
        ]);

        Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Meta alta',
            'meta_apoyo' => 5000,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_INACTIVA,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.campanas.index', [
            'rango_monto' => RangoMonto::Medio->value,
        ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Campanas/Index')
            ->where('filters.rango_monto', RangoMonto::Medio->value)
            ->has('campanas.data', 1)
            ->where('campanas.data.0.id', $medio->id)
        );
    }

    public function test_filtro_rango_bajo_en_donaciones_por_monto(): void
    {
        $admin = $this->crearUsuarioAdmin();
        [, $campana, $tipoPago] = $this->crearEmprendedorCampanaYTipo();

        Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'visitante_id' => null,
            'monto' => 100,
            'metodo' => 'efectivo',
            'estado_pago' => Donacion::ESTADO_VALIDADO,
            'referencia_pago' => 'A-1',
        ]);

        Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'visitante_id' => null,
            'monto' => 1500,
            'metodo' => 'efectivo',
            'estado_pago' => Donacion::ESTADO_VALIDADO,
            'referencia_pago' => 'A-2',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.donaciones.index', [
            'rango_monto' => RangoMonto::Bajo->value,
        ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Donaciones/Index')
            ->where('filters.rango_monto', RangoMonto::Bajo->value)
            ->has('donaciones.data', 1)
            ->where('donaciones.data.0.monto', '100.00')
        );
    }

    private function crearEmprendedor(): Emprendedor
    {
        return Emprendedor::query()->create([
            'nombre' => 'Test',
            'apellidos' => 'Emprendedor',
            'descripcion' => 'Descripción de prueba para tests',
            'fotografia' => null,
            'qr_url' => null,
            'estado' => 'activo',
            'meta_monto' => 1000,
        ]);
    }

    /**
     * @return array{0: Emprendedor, 1: Campana, 2: TipoPago}
     */
    private function crearEmprendedorCampanaYTipo(): array
    {
        $emprendedor = $this->crearEmprendedor();

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña test',
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

        return [$emprendedor, $campana, $tipoPago];
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
