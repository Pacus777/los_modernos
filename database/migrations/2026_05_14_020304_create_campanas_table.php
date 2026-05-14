<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de campañas.
     *
     * Se crea ahora porque la tabla donaciones necesita relacionarse
     * con una campaña mediante campana_id.
     */
    public function up(): void
    {
        Schema::create('campanas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('emprendedor_id')
                ->constrained('emprendedores')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('titulo');
            $table->decimal('meta_apoyo', 10, 2);
            $table->decimal('monto_recaudado', 10, 2)->default(0);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->enum('estado', [
                'activa',
                'inactiva',
                'finalizada',
            ])->default('activa');

            $table->timestamps();

            $table->index(['emprendedor_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campanas');
    }
};