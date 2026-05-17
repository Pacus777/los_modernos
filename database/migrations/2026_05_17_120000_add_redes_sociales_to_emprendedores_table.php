<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->string('whatsapp', 120)->nullable()->after('video_url');
            $table->string('instagram', 500)->nullable()->after('whatsapp');
            $table->string('facebook', 500)->nullable()->after('instagram');
            $table->string('tiktok', 500)->nullable()->after('facebook');
        });
    }

    public function down(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->dropColumn(['whatsapp', 'instagram', 'facebook', 'tiktok']);
        });
    }
};
