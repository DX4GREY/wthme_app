<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capture_moments', function (Blueprint $table) {
            $table->id();
            $table->string('kelompok'); // 1 baris per kelompok (15 kelompok)
            $table->string('foto_path');
            $table->text('caption')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();

            // Parameter penilaian panitia
            $table->unsignedTinyInteger('skor_kelengkapan')->nullable(); // 0-100
            $table->unsignedTinyInteger('skor_tema')->nullable();        // 0-100
            $table->unsignedTinyInteger('skor_estetika')->nullable();    // 0-100
            $table->unsignedSmallInteger('total_skor')->nullable();      // hasil sum

            $table->foreignId('dinilai_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dinilai_at')->nullable();

            // Hasil ranking (di-generate otomatis tiap kali ada penilaian baru)
            $table->unsignedTinyInteger('juara')->nullable(); // 1,2,3,4 dst (urutan ranking)
            $table->unsignedSmallInteger('poin')->nullable();  // 200/190/180/150

            $table->timestamps();

            $table->unique('kelompok');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capture_moments');
    }
};
