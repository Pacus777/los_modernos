<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Relaciona emprendedores con puntos físicos.
     *
     * Esto permite que un QR de punto físico muestre una lista de
     * emprendedores activos disponibles en esa ubicación.
     */
    public function up(): void
    {
        Schema::create('emprendedor_punto', function (Blueprint $table) {
            $table->id();

            $table->foreignId('punto_id')
                ->constrained('puntos_fisicos')
                ->cascadeOnDelete();

            $table->foreignId('emprendedor_id')
                ->constrained('emprendedores')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['punto_id', 'emprendedor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emprendedor_punto');
    }
};