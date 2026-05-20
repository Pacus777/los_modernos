<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo base de formas de pago (efectivo, QR, etc.) para donaciones y caja.
     */
    public function up(): void
    {
        Schema::create('tipos_pago', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 40)->unique();
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Campos para integración futura con pasarela
            |--------------------------------------------------------------------------
            |
            | proveedor permite saber si el método será procesado por:
            | - manual: efectivo o validación interna
            | - libelula: pago automático por pasarela
            | - banco: QR o transferencia externa
            |
            */

            $table->string('proveedor', 50)->default('manual')->index();

            $table->boolean('requiere_validacion_manual')->default(true);

            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_pago');
    }
};
