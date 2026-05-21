<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * S3-02: registro de webhooks ya procesados (idempotencia).
     *
     * La clave única (proveedor + idempotency_key) evita aplicar dos veces
     * el mismo evento si la pasarela reenvía el webhook.
     */
    public function up(): void
    {
        Schema::create('processed_webhooks', function (Blueprint $table) {
            $table->id();

            $table->string('proveedor', 50);

            $table->string('idempotency_key', 191);

            $table->string('event_id', 150)->nullable();

            $table->string('transaction_id', 150)->nullable();

            $table->foreignId('donacion_id')
                ->nullable()
                ->constrained('donaciones')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('webhook_log_id')
                ->nullable()
                ->constrained('webhook_logs')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('status', 30)->default('processing')->index();

            $table->unsignedSmallInteger('http_status')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(['proveedor', 'idempotency_key']);
            $table->index(['proveedor', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_webhooks');
    }
};
