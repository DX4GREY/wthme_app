<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suara_peserta_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suara_peserta_id')->constrained('suara_pesertas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['suara_peserta_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suara_peserta_reads');
    }
};