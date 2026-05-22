<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_peserta', function (Blueprint $table) {
            $table->string('metode')->default('qr')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_peserta', function (Blueprint $table) {
            $table->dropColumn('metode');
        });
    }
};