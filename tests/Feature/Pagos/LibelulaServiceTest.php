<?php

namespace Tests\Feature\Pagos;

use App\Exceptions\LibelulaApiException;
use App\Models\Campana;
use App\Models\Donacion;
use App\Models\Emprendedor;
use App\Models\TipoPago;
use App\Services\LibelulaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LibelulaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'libelula.app_key' => 'test-app-key',
            'libelula.fake_when_unconfigured' => false,
            'libelula.sandbox' => true,
        ]);
    }

    public function test_crear_deuda_guarda_transaction_id_y_checkout_url(): void
    {
        Http::fake([
            '*/rest/deuda/registrar' => Http::response([
                'error' => false,
                'mensaje' => 'OK',
                'id_transaccion' => 'TX-LIB-999',
                'url_pasarela_pagos' => 'https://pasarela.libelula.test/pagar/999',
                'qr_simple_url' => 'https://pasarela.libelula.test/qr/999.png',
            ], 200),
        ]);

        $donacion = $this->crearDonacionLibelulaPendiente();

        $respuesta = app(LibelulaService::class)->crearDeuda($donacion);

        $this->assertSame('TX-LIB-999', $respuesta->idTransaccion);
        $this->assertDatabaseHas('donaciones', [
            'id' => $donacion->id,
            'transaction_id' => 'TX-LIB-999',
            'checkout_url' => 'https://pasarela.libelula.test/pagar/999',
            'proveedor_pago' => Donacion::PROVEEDOR_LIBELULA,
        ]);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->method() === 'POST'
                && str_contains($request->url(), '/rest/deuda/registrar')
                && ($body['appkey'] ?? null) === 'test-app-key'
                && isset($body['identificador_deuda'])
                && isset($body['lineas_detalle_deuda']);
        });
    }

    public function test_modo_simulado_sin_app_key(): void
    {
        config(['libelula.app_key' => null, 'libelula.fake_when_unconfigured' => true]);

        Http::fake();

        $donacion = $this->crearDonacionLibelulaPendiente();

        $respuesta = app(LibelulaService::class)->crearDeuda($donacion);

        $this->assertTrue($respuesta->simulada);
        $this->assertNotNull($donacion->fresh()->transaction_id);
        Http::assertNothingSent();
    }

    public function test_lanza_excepcion_si_libelula_responde_error(): void
    {
        Http::fake([
            '*/rest/deuda/registrar' => Http::response([
                'error' => true,
                'mensaje' => 'Appkey inválida',
            ], 200),
        ]);

        $donacion = $this->crearDonacionLibelulaPendiente();

        $this->expectException(LibelulaApiException::class);
        $this->expectExceptionMessage('Appkey inválida');

        app(LibelulaService::class)->crearDeuda($donacion);
    }

    public function test_no_duplica_deuda_si_ya_tiene_transaction_id(): void
    {
        Http::fake();

        $donacion = $this->crearDonacionLibelulaPendiente();
        $donacion->update([
            'transaction_id' => 'TX-EXISTENTE',
            'checkout_url' => 'https://ya.registrada.test',
            'proveedor_pago' => Donacion::PROVEEDOR_LIBELULA,
        ]);

        $respuesta = app(LibelulaService::class)->crearDeuda($donacion->fresh());

        $this->assertSame('TX-EXISTENTE', $respuesta->idTransaccion);
        Http::assertNothingSent();
    }

    private function crearDonacionLibelulaPendiente(): Donacion
    {
        $emprendedor = Emprendedor::query()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Libélula',
            'descripcion' => null,
            'estado' => 'activo',
            'meta_monto' => 0,
        ]);

        $campana = Campana::query()->create([
            'emprendedor_id' => $emprendedor->id,
            'titulo' => 'Campaña Libélula',
            'meta_apoyo' => 500,
            'monto_recaudado' => 0,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
            'estado' => Campana::ESTADO_ACTIVA,
        ]);

        $tipoPago = TipoPago::query()->create([
            'codigo' => 'tarjeta',
            'nombre' => 'Tarjeta',
            'proveedor' => TipoPago::PROVEEDOR_LIBELULA,
            'activo' => true,
            'requiere_validacion_manual' => false,
        ]);

        return Donacion::query()->create([
            'campana_id' => $campana->id,
            'tipo_pago_id' => $tipoPago->id,
            'monto' => 75.50,
            'moneda' => Donacion::MONEDA_BOB,
            'metodo' => 'tarjeta',
            'estado_pago' => Donacion::ESTADO_PENDIENTE,
            'referencia_pago' => 'WAYNA-LIB-TEST',
            'payment_uuid' => (string) \Illuminate\Support\Str::uuid(),
        ]);
    }
}
