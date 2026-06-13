<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p3k_barang_kebutuhan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');
            $table->enum('kategori', ['kelompok', 'individu']); // barang kelompok vs barang individu
            $table->integer('jumlah_kebutuhan');
            $table->string('satuan')->default('pcs');
            $table->string('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p3k_barang_kebutuhan');
    }
};