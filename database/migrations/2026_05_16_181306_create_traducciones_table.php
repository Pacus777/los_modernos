<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * T-A34: Tabla reutilizable para traducciones dinámicas.
     *
     * Sirve para traducir campos de distintas entidades:
     * - emprendedores.descripcion
     * - emprendedores.tipo_emprendimiento
     * - campanas.titulo
     * - campanas.descripcion futura
     * - landing, bot, puntos u otros módulos.
     */
    public function up(): void
    {
        Schema::create('traducciones', function (Blueprint $table) {
            $table->id();

            $table->string('entidad_tipo', 120);
            $table->unsignedBigInteger('entidad_id')->nullable();

            $table->string('campo', 120);

            $table->string('idioma_origen', 10)->default('es');
            $table->string('idioma_destino', 10);

            $table->string('texto_original_hash', 64);
            $table->longText('texto_original');
            $table->longText('texto_traducido');

            $table->string('proveedor', 50)->default('deepl');

            $table->timestamps();

            $table->index(['entidad_tipo', 'entidad_id']);
            $table->index(['campo', 'idioma_destino']);
            $table->unique([
                'entidad_tipo',
                'entidad_id',
                'campo',
                'idioma_origen',
                'idioma_destino',
                'texto_original_hash',
            ], 'traducciones_unicas_por_texto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traducciones');
    }
};