<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mentorings', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('mentor_id');
        $table->string('nama_kegiatan');
        $table->string('kelompok');
        $table->date('tanggal');
        $table->timestamps();

        $table->foreign('mentor_id')->references('id')->on('users');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentorings');
    }
};
