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

class DonacionCsvExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_exportar_donaciones_csv(): void
    {
        $admin = $this->crearAdmin();
        $this->crearDonacionEjemplo();

        $response = $this->actingAs($admin)->get(route('admin.donaciones.exportar-csv'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $cuerpo = $response->getContent();
        $this->assertStringContainsString('ID', $cuerpo);
        $this->assertStringContainsString('Monto BOB', $cuerpo);
        $this->assertStringContainsString('validado', $cuerpo);
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

    private function crearDonacionEjemplo(): Donacion
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Export',
            'descripcion' => null,
            'fotografia' => null,
            'qr_url' => null,
            'estado' => 'activo',
            'meta_monto' => 0,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña CSV',
            'meta_apoyo' => 100,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoPago = TipoPago::query()->create([
            'codigo' => 'efectivo_vis',
            'nombre' => 'Efectivo',
            'descripcion' => null,
            'activo' => true,
        ]);

        return Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 75.5,
            'metodo' => 'efectivo_vis',
            'estado_pago' => Donacion::ESTADO_VALIDADO,
        ]);
    }
}
