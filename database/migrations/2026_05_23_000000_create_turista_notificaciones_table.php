<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * S4-09: Notificaciones in-app persistidas para turista con cuenta.
     */
    public function up(): void
    {
        Schema::create('turista_notificaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('emprendedor_id')
                ->nullable()
                ->constrained('emprendedores')
                ->nullOnDelete();

            $table->foreignId('emprendedor_post_id')
                ->nullable()
                ->constrained('emprendedor_posts')
                ->nullOnDelete();

            $table->foreignId('campana_id')
                ->nullable()
                ->constrained('campanas')
                ->nullOnDelete();

            $table->string('tipo', 40);
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('url', 500)->nullable();
            $table->timestamp('leida_at')->nullable();

            $table->timestamps();

            // Índices para búsquedas eficientes
            $table->index(['user_id', 'leida_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['tipo']);

            // Unicidad para evitar notificaciones duplicadas del mismo post o campaña
            $table->unique(
                ['user_id', 'tipo', 'emprendedor_post_id'],
                'turista_notif_user_tipo_post_unique'
            );
            $table->unique(
                ['user_id', 'tipo', 'campana_id'],
                'turista_notif_user_tipo_campana_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turista_notificaciones');
    }
};
