<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p3k_pj_kelompok', function (Blueprint $table) {
            $table->id();
            $table->string('kelompok')->unique();
            $table->foreignId('pj_p3k_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p3k_pj_kelompok');
    }
};