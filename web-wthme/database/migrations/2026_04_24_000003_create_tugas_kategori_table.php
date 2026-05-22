<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tugas');              // misal: "Tugas 1 - Essay Motivasi"
            $table->text('deskripsi')->nullable();     // penjelasan tugas
            $table->dateTime('deadline')->nullable();  // batas pengumpulan
            $table->boolean('aktif')->default(true);   // apakah peserta bisa submit
            $table->string('tipe_file')->default('semua'); // semua / pdf / gambar
            $table->unsignedBigInteger('maks_ukuran')->default(10240); // kb, default 10MB
            $table->integer('urutan')->default(0);     // urutan tampil di tabel
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_kategori');
    }
};
