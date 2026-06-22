<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stok terpakai global per barang individu — satu baris per barang,
     * tidak dipecah per kelompok. total_terkumpul dihitung on-the-fly
     * dari SUM per-kelompok (p3k_stok_individu), total_terpakai dicatat
     * di sini secara global karena pemakaian di lapangan tidak perlu
     * dilacak per kelompok asal.
     */
    public function up(): void
    {
        Schema::create('p3k_stok_global', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p3k_barang_kebutuhan_id')
                ->unique()
                ->constrained('p3k_barang_kebutuhan')
                ->onDelete('cascade');
            $table->integer('total_terpakai')->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p3k_stok_global');
    }
};
