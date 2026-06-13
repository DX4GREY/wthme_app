<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_abang_kbms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('angkatan'); // '2021', '2022', '2023', '2024'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_abang_kbms');
    }
};