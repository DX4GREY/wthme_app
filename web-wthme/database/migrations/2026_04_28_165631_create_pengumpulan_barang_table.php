<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumpulan_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_kebutuhan_id')->constrained('barang_kebutuhan')->onDelete('cascade');
            $table->integer('kelompok');
            $table->integer('jumlah_terkumpul')->default(0);
            $table->string('foto_bukti')->nullable(); // path file foto
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Satu record per barang per kelompok
            $table->unique(['barang_kebutuhan_id', 'kelompok']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_barang');
    }
};