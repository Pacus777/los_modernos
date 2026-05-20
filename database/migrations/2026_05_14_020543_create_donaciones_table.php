<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla central de donaciones.
     *
     * estado_pago controla el ciclo:
     * pendiente = donación registrada, pero no verificada.
     * validado  = admin o cajero confirmó el pago.
     * rechazado = no se pudo verificar el pago.
     */
    public function up(): void
    {
        Schema::create('donaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campana_id')
                ->constrained('campanas')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('tipo_pago_id')
                ->constrained('tipos_pago')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('visitante_id')
                ->nullable()
                ->constrained('visitantes')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('monto', 10, 2);

            /*
            |--------------------------------------------------------------------------
            | Moneda principal
            |--------------------------------------------------------------------------
            |
            | El sistema sigue trabajando en bolivianos.
            | USD solo es referencia visual para turistas.
            */

            $table->string('moneda', 3)->default('BOB');

            $table->string('metodo', 50);

            $table->enum('estado_pago', [
                'pendiente',
                'validado',
                'rechazado',
            ])->default('pendiente');

            $table->string('referencia_pago', 150)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Campos para pasarela Libélula / pagos externos
            |--------------------------------------------------------------------------
            |
            | payment_uuid evita doble envío desde frontend.
            | transaction_id guardará el identificador devuelto por Libélula.
            | proveedor_pago permite distinguir manual, libelula, banco, etc.
            | estado_proveedor guarda el estado recibido por la pasarela.
            |
            */

            $table->uuid('payment_uuid')->nullable()->unique();

            $table->string('transaction_id', 150)->nullable()->unique();

            $table->string('proveedor_pago', 50)->nullable()->index();

            $table->string('estado_proveedor', 60)->nullable()->index();

            $table->string('checkout_url', 500)->nullable();

            $table->timestamp('pagado_en')->nullable();

            $table->json('metadata_pago')->nullable();

            $table->timestamps();

            $table->index(['estado_pago', 'created_at']);
            $table->index(['campana_id', 'estado_pago']);
            $table->index(['tipo_pago_id', 'estado_pago']);
            $table->index(['proveedor_pago', 'estado_proveedor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donaciones');
    }
};