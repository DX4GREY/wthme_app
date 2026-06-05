<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // KUNCI UTAMA: Kita tambahkan 'izin' ke dalam daftar pilihan ENUM menggunakan SQL Native
        DB::statement("ALTER TABLE absensi_peserta MODIFY COLUMN status ENUM('hadir', 'izin', 'tidak_hadir') DEFAULT 'hadir'");
    }

    public function down(): void
    {
        // Kembalikan ke format awal jika dilakukan rollback
        DB::statement("ALTER TABLE absensi_peserta MODIFY COLUMN status ENUM('hadir', 'tidak_hadir') DEFAULT 'hadir'");
    }
};