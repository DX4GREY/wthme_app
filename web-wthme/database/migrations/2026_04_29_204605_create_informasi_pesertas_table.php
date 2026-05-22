<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('informasi_pesertas', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('url_link');
            $table->string('kategori'); // misal: 'Pengumuman', 'Materi', 'Link Zoom'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informasi_pesertas');
    }
};
