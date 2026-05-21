<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * S4-01: turistas que siguen a un emprendedor (feed / notificaciones futuras).
     */
    public function up(): void
    {
        Schema::create('emprendedor_seguidores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('emprendedor_id')
                ->constrained('emprendedores')
                ->cascadeOnDelete();

            $table->foreignId('visitante_id')
                ->constrained('visitantes')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['emprendedor_id', 'visitante_id'],
                'emprendedor_seguidores_unique',
            );

            $table->index(['visitante_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emprendedor_seguidores');
    }
};
