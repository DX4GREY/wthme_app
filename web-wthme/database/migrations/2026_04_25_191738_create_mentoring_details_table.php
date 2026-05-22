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
        Schema::create('mentoring_details', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('mentoring_id');
        $table->unsignedBigInteger('peserta_id');
        $table->enum('kehadiran', ['Hadir', 'Izin', 'Alpha'])->default('Alpha');
        $table->text('keterangan')->nullable();
        $table->timestamps();

        $table->foreign('mentoring_id')->references('id')->on('mentorings')->onDelete('cascade');
        $table->foreign('peserta_id')->references('id')->on('users');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentoring_details');
    }
};
