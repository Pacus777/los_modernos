<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * S2-01: Registro de webhooks recibidos desde pasarelas externas.
     *
     * Esta tabla no confirma pagos por sí sola.
     * Solo guarda evidencia de cada webhook recibido para trazabilidad,
     * auditoría y depuración.
     */
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();

            $table->string('proveedor', 50)->index();

            $table->string('event_id', 150)->nullable()->index();

            $table->string('transaction_id', 150)->nullable()->index();

            $table->foreignId('donacion_id')
                ->nullable()
                ->constrained('donaciones')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('evento', 100)->nullable();

            $table->string('estado', 50)->default('recibido')->index();

            $table->json('headers')->nullable();

            $table->json('payload')->nullable();

            $table->timestamp('recibido_en')->nullable();

            $table->timestamp('procesado_en')->nullable();

            $table->text('error')->nullable();

            $table->timestamps();

            $table->index(['proveedor', 'estado']);
            $table->index(['proveedor', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};