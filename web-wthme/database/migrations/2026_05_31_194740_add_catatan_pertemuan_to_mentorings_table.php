<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mentorings', function (Blueprint $table) {
            // Menambahkan kolom catatan setelah kolom tanggal
            $table->text('catatan_pertemuan')->nullable()->after('tanggal');
        });
    }

    public function down(): void
    {
        Schema::table('mentorings', function (Blueprint $table) {
            $table->dropColumn('catatan_pertemuan');
        });
    }
};