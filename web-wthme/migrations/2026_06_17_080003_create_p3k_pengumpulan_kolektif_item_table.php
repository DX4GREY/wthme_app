<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jumlah aktual yang dibawa perwakilan untuk satu jenis barang individu,
     * dalam satu pengumpulan kolektif. Target (kebutuhan) untuk baris ini
     * dihitung otomatis di level aplikasi:
     *   target = p3k_barang_kebutuhan.jumlah_kebutuhan x jumlah_anggota_pengumpulan
     */
    public function up(): void
    {
        Schema::create('p3k_pengumpulan_kolektif_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengumpulan_kolektif_id')
                ->constrained('p3k_pengumpulan_kolektif')
                ->onDelete('cascade');
            $table->foreignId('p3k_barang_kebutuhan_id')
                ->constrained('p3k_barang_kebutuhan')
                ->onDelete('cascade');

            $table->integer('jumlah_dibawa')->default(0);

            $table->timestamps();

            $table->unique(
                ['pengumpulan_kolektif_id', 'p3k_barang_kebutuhan_id'],
                'p3k_pk_item_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p3k_pengumpulan_kolektif_item');
    }
};
