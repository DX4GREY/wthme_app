<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::create('daily_absensi_passwords', function (Blueprint $table) {
    $table->id();
    $table->date('tanggal')->unique();
    $table->string('password');
    $table->string('password_tampil')->nullable(); // Plain password for display (max 50 chars)
    $table->unsignedBigInteger('dibuat_oleh')->nullable();
    $table->timestamp('dibuat_pada')->useCurrent();
    $table->timestamps();

    $table->index('tanggal');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_absensi_passwords');
    }
};