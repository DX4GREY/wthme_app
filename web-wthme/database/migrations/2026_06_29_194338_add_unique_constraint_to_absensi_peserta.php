<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_peserta', function (Blueprint $table) {
            $table->unique(['user_id', 'qr_session_id'], 'uniq_user_per_session');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_peserta', function (Blueprint $table) {
            $table->dropUnique('uniq_user_per_session');
        });
    }
};