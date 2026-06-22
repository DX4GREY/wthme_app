<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p3k_pengumpulan_kolektif_anggota', function (Blueprint $table) {
            // Drop FK dulu sebelum drop unique — MySQL requirement
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);

            $table->string('menu')->nullable()->after('pengumpulan_kolektif_id');
        });

        // Backfill menu dari tabel induk
        DB::statement("
            UPDATE p3k_pengumpulan_kolektif_anggota a
            JOIN p3k_pengumpulan_kolektif k ON k.id = a.pengumpulan_kolektif_id
            SET a.menu = k.menu
        ");

        Schema::table('p3k_pengumpulan_kolektif_anggota', function (Blueprint $table) {
            $table->string('menu')->default('p3k')->change();

            // Pasang ulang FK
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Composite unique per menu
            $table->unique(['user_id', 'menu'], 'pkka_user_menu_unique');
        });
    }

    public function down(): void
    {
        Schema::table('p3k_pengumpulan_kolektif_anggota', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique('pkka_user_menu_unique');
            $table->dropColumn('menu');
            $table->unique('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
