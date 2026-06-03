<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tugas_pengumpulan', function (Blueprint $table) {
            // Mengubah tipe data menjadi TEXT agar muat hingga 65.000 karakter
            $table->text('file_path')->change();
        });
    }

    public function down(): void
    {
        Schema::table('tugas_pengumpulan', function (Blueprint $table) {
            // Kembalikan ke VARCHAR jika di-rollback
            $table->string('file_path', 255)->change();
        });
    }
};