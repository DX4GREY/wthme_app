<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nim')->unique()->nullable(); // NIM untuk peserta
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['peserta','panitia','admin','bendahara','mentor', 'korlap'])->default('peserta');
            $table->string('angkatan')->nullable();
            $table->string('kelompok')->nullable();   // untuk peserta
            $table->string('divisi')->nullable();      // untuk panitia
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->boolean('must_change_password')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};