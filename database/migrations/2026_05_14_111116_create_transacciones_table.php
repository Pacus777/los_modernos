<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trazabilidad de aportes (PB-12 / T-34).
     *
     * Clave primaria UUID para identificador único global y difícil de adivinar.
     * metadatos almacena contexto variable (idioma, referencias, etc.) sin columnas extra.
     */
    public function up(): void
    {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('origen', 255);
            $table->string('destino', 255);
            $table->string('estado', 50);

            $table->jsonb('metadatos')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transacciones');
    }
};
