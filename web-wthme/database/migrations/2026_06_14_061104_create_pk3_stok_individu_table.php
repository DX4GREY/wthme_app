<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p3k_stok_individu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p3k_barang_kebutuhan_id')->unique()->constrained('p3k_barang_kebutuhan')->onDelete('cascade');

            // total_terkumpul = SUM(jumlah_dibawa) dari semua peserta, di-recalc otomatis
            $table->integer('total_terkumpul')->default(0);

            // total_terpakai = dikurangi manual oleh panitia (pool global, tidak per kelompok)
            $table->integer('total_terpakai')->default(0);

            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p3k_stok_individu');
    }
};
