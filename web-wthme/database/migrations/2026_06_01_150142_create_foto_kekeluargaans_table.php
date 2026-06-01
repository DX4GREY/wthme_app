<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_kekeluargaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengirim_id')->constrained('users')->onDelete('cascade'); // Si A yang upload
            $table->foreignId('teman_id')->constrained('users')->onDelete('cascade');    // Si B yang di-tag
            $table->string('foto');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            // Mencegah upload foto berulang dengan teman yang sama
            $table->unique(['pengirim_id', 'teman_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_kekeluargaans');
    }
};