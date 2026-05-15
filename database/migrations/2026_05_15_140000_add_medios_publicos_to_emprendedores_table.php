<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Medios del perfil público (T-A22): empresa, galería y video opcional.
     */
    public function up(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->string('foto_empresa')->nullable()->after('fotografia');
            $table->json('galeria')->nullable()->after('foto_empresa');
            $table->string('video_url')->nullable()->after('galeria');
        });
    }

    public function down(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->dropColumn(['foto_empresa', 'galeria', 'video_url']);
        });
    }
};
