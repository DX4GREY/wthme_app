<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Barang P3K kini punya dua dimensi:
     *  - tipe  : 'kelompok' | 'individu'  (RENAME dari kolom lama 'kategori')
     *  - menu  : 'logistik' | 'konsumsi' | 'p3k'  (BARU — kategori menu utama)
     *
     * Data lama di-backfill: semua baris yang ada sekarang dianggap menu='p3k'
     * karena modul awalnya hanya untuk P3K.
     */
    public function up(): void
    {
        Schema::table('p3k_barang_kebutuhan', function (Blueprint $table) {
            // Rename kategori → tipe (kelompok/individu)
            $table->renameColumn('kategori', 'tipe');

            // Kolom menu baru, nullable dulu supaya bisa backfill
            $table->string('menu')->nullable()->after('tipe');
        });

        // Backfill semua data lama → p3k
        DB::table('p3k_barang_kebutuhan')->update(['menu' => 'p3k']);

        // Sekarang ubah jadi NOT NULL dengan default
        Schema::table('p3k_barang_kebutuhan', function (Blueprint $table) {
            $table->string('menu')->default('p3k')->change();
        });
    }

    public function down(): void
    {
        Schema::table('p3k_barang_kebutuhan', function (Blueprint $table) {
            $table->renameColumn('tipe', 'kategori');
            $table->dropColumn('menu');
        });
    }
};
