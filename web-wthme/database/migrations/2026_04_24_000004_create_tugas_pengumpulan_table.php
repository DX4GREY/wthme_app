<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas_pengumpulan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tugas_kategori_id')->constrained('tugas_kategori')->onDelete('cascade');
            $table->string('nama');
            $table->string('nim');
            $table->string('kelompok');
            $table->string('file_path');               // storage path
            $table->string('file_nama_asli');          // original filename
            $table->string('file_ekstensi', 10);       // pdf, jpg, png, dll
            $table->unsignedBigInteger('file_ukuran'); // bytes
            $table->enum('status', ['tepat_waktu', 'terlambat'])->default('tepat_waktu');
            $table->text('catatan')->nullable();       // catatan dari peserta
            $table->timestamp('dikumpulkan_at');
            $table->timestamps();

            // Satu peserta hanya bisa kumpul sekali per tugas (bisa update)
            $table->unique(['user_id', 'tugas_kategori_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_pengumpulan');
    }
};
