<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_password_history', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('password_tampil');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['tanggal', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_password_history');
    }
};