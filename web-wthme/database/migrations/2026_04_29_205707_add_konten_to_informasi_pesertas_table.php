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
        Schema::table('informasi_pesertas', function (Blueprint $table) {
            $table->text('konten')->nullable()->after('judul'); // Kolom untuk teks pengumuman
            $table->string('url_link')->nullable()->change();  // Ubah link jadi opsional
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('informasi_pesertas', function (Blueprint $table) {
            //
        });
    }
};
