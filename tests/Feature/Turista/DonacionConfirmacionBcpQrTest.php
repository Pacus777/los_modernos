<?php

namespace Tests\Feature\Turista;

use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\TipoPago;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DonacionConfirmacionBcpQrTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_confirmacion_muestra_qr_bcp_estatico_para_pago_qr(): void
    {
        $donacion = $this->crearDonacionQrPendiente();

        $this->get(route('turista.donaciones.confirmacion', $donacion))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Turista/Confirmacion')
                ->where('confirmacion.usa_qr_bcp_estatico', true)
                ->where('confirmacion.referencia_pago', $donacion->referencia_pago)
                ->has('wayna_bcp_qr.imagen_url')
                ->where('qr_pago_url', null));
    }

    private function crearDonacionQrPendiente(): Donacion
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Luis',
            'apellidos' => 'QR',
            'descripcion' => 'Descripción de prueba con más de diez caracteres.',
            'tipo_emprendimiento' => 'artesania',
            'departamento' => 'la_paz',
            'estado' => 'activo',
            'meta_monto' => 100,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña QR',
            'meta_apoyo' => 500,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addMonth(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoQr = TipoPago::query()->create([
            'codigo' => 'qr',
            'nombre' => 'QR / billetera móvil',
            'proveedor' => TipoPago::PROVEEDOR_BANCO,
            'activo' => true,
        ]);

        return Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoQr->id,
            'monto' => 25,
            'moneda' => Donacion::MONEDA_BOB,
            'metodo' => 'qr',
            'estado_pago' => Donacion::ESTADO_PENDIENTE,
            'referencia_pago' => 'WAYNA-BCP-'.Str::upper(Str::random(6)),
            'payment_uuid' => (string) Str::uuid(),
        ]);
    }
}
