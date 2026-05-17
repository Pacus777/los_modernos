<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_cambio', function (Blueprint $table) {
            $table->id();

            $table->string('base', 10)->default('USD');
            $table->string('quote', 10)->default('BOB');

            $table->decimal('compra', 12, 4)->nullable();
            $table->decimal('venta', 12, 4);
            $table->decimal('promedio', 12, 4)->nullable();

            $table->string('fuente', 120);
            $table->string('tipo', 60)->default('referencial');

            $table->timestamp('consultado_en')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['base', 'quote', 'tipo']);
            $table->index('consultado_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_cambio');
    }
};