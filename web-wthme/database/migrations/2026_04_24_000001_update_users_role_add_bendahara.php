<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // Gabungkan SEMUA role yang kamu butuhkan di sini
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('peserta','panitia','admin','bendahara','mentor', 'korlap') NOT NULL DEFAULT 'peserta'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Kembalikan ke pilihan standar jika rollback
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('peserta','panitia','admin','bendahara', 'mentor') NOT NULL DEFAULT 'peserta'");
        }
    }
};