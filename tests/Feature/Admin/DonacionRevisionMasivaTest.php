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

class DonacionRevisionMasivaTest extends TestCase
{
    use RefreshDatabase;

    public function test_validacion_masiva_solo_procesa_pendientes_y_suma_monto_una_vez(): void
    {
        $admin = $this->crearUsuarioAdmin();
        [$campana, $tipoPago] = $this->crearCampanaYTipo();

        $pendienteA = $this->crearDonacion($campana, $tipoPago, 50, Donacion::ESTADO_PENDIENTE);
        $pendienteB = $this->crearDonacion($campana, $tipoPago, 30, Donacion::ESTADO_PENDIENTE);
        $yaValidada = $this->crearDonacion($campana, $tipoPago, 100, Donacion::ESTADO_VALIDADO);

        $response = $this->actingAs($admin)->patch(route('admin.donaciones.revision-masiva'), [
            'ids' => [$pendienteA->id, $pendienteB->id, $yaValidada->id],
            'accion' => 'validar',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSame(Donacion::ESTADO_VALIDADO, $pendienteA->fresh()->estado_pago);
        $this->assertSame(Donacion::ESTADO_VALIDADO, $pendienteB->fresh()->estado_pago);
        $this->assertEquals('180.00', $campana->fresh()->monto_recaudado);

        $responseDuplicado = $this->actingAs($admin)->patch(route('admin.donaciones.revision-masiva'), [
            'ids' => [$pendienteA->id, $pendienteB->id],
            'accion' => 'validar',
        ]);

        $responseDuplicado->assertRedirect();
        $responseDuplicado->assertSessionHas('error');
        $this->assertEquals('180.00', $campana->fresh()->monto_recaudado);
    }

    public function test_rechazo_masivo_de_pendientes(): void
    {
        $admin = $this->crearUsuarioAdmin();
        [$campana, $tipoPago] = $this->crearCampanaYTipo();

        $pendiente = $this->crearDonacion($campana, $tipoPago, 25, Donacion::ESTADO_PENDIENTE);

        $response = $this->actingAs($admin)->patch(route('admin.donaciones.revision-masiva'), [
            'ids' => [$pendiente->id],
            'accion' => 'rechazar',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSame(Donacion::ESTADO_RECHAZADO, $pendiente->fresh()->estado_pago);
        $this->assertEquals('0.00', $campana->fresh()->monto_recaudado);
    }

    /**
     * @return array{0: Campana, 1: TipoPago}
     */
    private function crearCampanaYTipo(): array
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Masiva',
            'descripcion' => 'Prueba',
            'estado' => 'activo',
            'meta_monto' => 1000,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña masiva',
            'meta_apoyo' => 1000,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoPago = TipoPago::query()->create([
            'codigo' => 'efectivo_masivo',
            'nombre' => 'Efectivo',
            'descripcion' => null,
            'activo' => true,
        ]);

        return [$campana, $tipoPago];
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
