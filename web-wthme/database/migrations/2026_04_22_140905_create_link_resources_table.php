<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_resources', function (Blueprint $table) {
            $table->id();
            $table->string('nama');          // nama tampilan
            $table->string('url');           // link drive
            $table->string('ikon')->nullable(); // nama ikon
            $table->enum('untuk', ['panitia', 'semua']);
            $table->integer('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_resources');
    }
};