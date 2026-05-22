<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_code')->unique();  // kode unik QR
            $table->string('nama_sesi');               // misal: "Hari 1 - Pembukaan"
            $table->enum('untuk', ['peserta', 'panitia']);
            $table->boolean('aktif')->default(true);
            $table->timestamp('berlaku_hingga')->nullable();
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_sessions');
    }
};