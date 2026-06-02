<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('riwayat_penyakit', function (Blueprint $table) {
            // Default null (artinya belum diklasifikasikan / Pita Hijau/Normal)
            $table->string('warna_pita')->nullable()->after('keterangan_tambahan');
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_penyakit', function (Blueprint $table) {
            $table->dropColumn('warna_pita');
        });
    }
};
