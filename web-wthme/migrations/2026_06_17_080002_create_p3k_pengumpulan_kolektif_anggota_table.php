<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Anggota yang tercakup dalam satu pengumpulan kolektif — termasuk
     * perwakilan itu sendiri (selalu ikut tercatat di sini sebagai 1 baris).
     *
     * `user_id` dibuat UNIQUE secara GLOBAL (bukan per pengumpulan) untuk
     * menegakkan aturan: satu peserta hanya boleh terdaftar di SATU
     * pengumpulan saja — tidak boleh nitip ke dua perwakilan sekaligus,
     * dan tidak boleh jadi perwakilan sekaligus nitip ke orang lain.
     */
    public function up(): void
    {
        Schema::create('p3k_pengumpulan_kolektif_anggota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengumpulan_kolektif_id')
                ->constrained('p3k_pengumpulan_kolektif')
                ->onDelete('cascade');

            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p3k_pengumpulan_kolektif_anggota');
    }
};
