<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p3k_obat_pribadi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // peserta yang lapor
            $table->string('kelompok');
            $table->string('penyakit'); // jenis penyakit / alergi / kondisi
            $table->string('nama_obat')->nullable();
            $table->text('catatan')->nullable();

            $table->foreignId('pj_p3k_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('sudah_diserahkan')->default(false);
            $table->string('foto_bukti')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p3k_obat_pribadi');
    }
};