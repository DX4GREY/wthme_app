<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p3k_stok_individu', function (Blueprint $table) {
            // 1. Drop FK yang pakai unique index ini dulu
            $table->dropForeign(['p3k_barang_kebutuhan_id']);

            // 2. Sekarang baru bisa drop unique index-nya
            $table->dropUnique(['p3k_barang_kebutuhan_id']);

            // 3. Tambah kolom kelompok
            $table->string('kelompok')->after('p3k_barang_kebutuhan_id');

            // 4. Buat ulang FK (tanpa unique, karena unique-nya nanti composite)
            $table->foreign('p3k_barang_kebutuhan_id')
                  ->references('id')
                  ->on('p3k_barang_kebutuhan')
                  ->onDelete('cascade');

            // 5. Unique composite: satu barang boleh muncul banyak kali, tapi tiap (barang, kelompok) unik
            $table->unique(['p3k_barang_kebutuhan_id', 'kelompok']);
        });
    }

    public function down(): void
    {
        Schema::table('p3k_stok_individu', function (Blueprint $table) {
            // Balik: drop composite unique + FK
            $table->dropUnique(['p3k_barang_kebutuhan_id', 'kelompok']);
            $table->dropForeign(['p3k_barang_kebutuhan_id']);
            $table->dropColumn('kelompok');

            // Buat ulang FK + unique seperti semula
            $table->foreignId('p3k_barang_kebutuhan_id')
                  ->unique()
                  ->constrained('p3k_barang_kebutuhan')
                  ->onDelete('cascade');
        });
    }
};