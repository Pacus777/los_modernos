<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * S3-09: registro inmutable de acciones críticas (seguridad y cumplimiento).
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action', 80)->index();

            $table->nullableMorphs('subject');

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['action', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
