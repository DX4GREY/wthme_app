<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('qr_session_id')->constrained('qr_sessions')->onDelete('cascade');
            $table->string('nama');
            $table->string('nim');
            $table->string('angkatan');
            $table->string('kelompok');
            $table->enum('status', ['hadir', 'tidak_hadir'])->default('hadir');
            $table->string('ip_address')->nullable();
            $table->timestamp('waktu_absen');
            $table->timestamps();
            
            // Satu user hanya bisa absen sekali per sesi
            $table->unique(['user_id', 'qr_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_peserta');
    }
};