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

            $table->string('metodo', 50);

            $table->enum('estado_pago', [
                'pendiente',
                'validado',
                'rechazado',
            ])->default('pendiente');

            $table->string('referencia_pago', 150)->nullable();

            $table->timestamps();

            $table->index(['estado_pago', 'created_at']);
            $table->index(['campana_id', 'estado_pago']);
            $table->index(['tipo_pago_id', 'estado_pago']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donaciones');
    }
};