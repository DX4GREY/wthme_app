<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengumpulan_barang', function (Blueprint $table) {
            $table->foreignId('panitia_penerima_id')->nullable()->after('jumlah_terkumpul')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pengumpulan_barang', function (Blueprint $table) {
            $table->dropForeign(['panitia_penerima_id']);
            $table->dropColumn('panitia_penerima_id');
        });
    }
};