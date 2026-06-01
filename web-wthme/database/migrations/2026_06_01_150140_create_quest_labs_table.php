<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_labs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_lab'); // 'Lab 1', 'Lab 2', 'Lab 3', 'Lab 4'
            $table->string('foto_selfie');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('submitted_at')->nullable(); // Mengunci waktu kirim asli peserta
            $table->timestamps();
            
            // Mencegah user spam upload di lab yang sama
            $table->unique(['user_id', 'nama_lab']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_labs');
    }
};