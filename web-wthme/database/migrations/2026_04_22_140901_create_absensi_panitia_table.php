<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_panitia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('qr_session_id')->constrained('qr_sessions')->onDelete('cascade');
            $table->string('nama');
            $table->string('nim');
            $table->string('divisi');
            $table->enum('status', ['hadir', 'tidak_hadir'])->default('hadir');
            $table->string('ip_address')->nullable();
            $table->timestamp('waktu_absen');
            $table->timestamps();
            
            $table->unique(['user_id', 'qr_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_panitia');
    }
};