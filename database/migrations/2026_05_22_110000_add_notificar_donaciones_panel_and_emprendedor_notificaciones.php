<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->boolean('notificar_donaciones_panel')
                ->default(true)
                ->after('notificar_donaciones_email');
        });

        Schema::create('emprendedor_notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emprendedor_id')->constrained('emprendedores')->cascadeOnDelete();
            $table->foreignId('donacion_id')->nullable()->constrained('donaciones')->nullOnDelete();
            $table->string('tipo', 40);
            $table->string('titulo');
            $table->text('mensaje');
            $table->decimal('monto', 12, 2)->nullable();
            $table->timestamp('leida_at')->nullable();
            $table->timestamps();

            $table->unique(['emprendedor_id', 'donacion_id', 'tipo'], 'emp_notif_donacion_tipo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emprendedor_notificaciones');

        Schema::table('emprendedores', function (Blueprint $table) {
            $table->dropColumn('notificar_donaciones_panel');
        });
    }
};
