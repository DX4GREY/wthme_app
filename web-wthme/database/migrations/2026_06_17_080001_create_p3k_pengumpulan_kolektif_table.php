<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Header "pengumpulan kolektif": satu baris = satu perwakilan kelompok yang
     * mengumpulkan barang P3K individu untuk dirinya sendiri + rekan-rekan yang
     * menitipkan barang ke dia. Satu kelompok boleh punya banyak perwakilan
     * (banyak baris di tabel ini dengan nilai `kelompok` yang sama).
     */
    public function up(): void
    {
        Schema::create('p3k_pengumpulan_kolektif', function (Blueprint $table) {
            $table->id();

            // Perwakilan = peserta yang membuka & mengisi form pengumpulan ini.
            // unique: satu peserta hanya boleh jadi perwakilan untuk SATU pengumpulan.
            $table->foreignId('perwakilan_user_id')->unique()->constrained('users')->onDelete('cascade');

            // Denormalisasi dari kelompok perwakilan, supaya query per-kelompok cepat
            // dan tetap konsisten meski data user berubah di kemudian hari.
            $table->string('kelompok');

            $table->string('foto_bukti')->nullable();
            $table->boolean('is_validated')->default(false);

            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('kelompok');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p3k_pengumpulan_kolektif');
    }
};
