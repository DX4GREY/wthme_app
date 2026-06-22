<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p3k_pengumpulan_kolektif', function (Blueprint $table) {
            // 1) Drop FK dulu — MySQL tidak izinkan drop unique index
            //    selama FK masih menggunakannya
            $table->dropForeign(['perwakilan_user_id']);

            // 2) Sekarang baru boleh drop unique
            $table->dropUnique(['perwakilan_user_id']);

            // 3) Tambah kolom menu (nullable untuk backfill)
            $table->string('menu')->nullable()->after('kelompok');
        });

        // 4) Backfill → p3k
        DB::table('p3k_pengumpulan_kolektif')->update(['menu' => 'p3k']);

        Schema::table('p3k_pengumpulan_kolektif', function (Blueprint $table) {
            // 5) Jadikan NOT NULL
            $table->string('menu')->default('p3k')->change();

            // 6) Pasang ulang FK (tanpa unique — unique sekarang composite)
            $table->foreign('perwakilan_user_id')->references('id')->on('users')->onDelete('cascade');

            // 7) Composite unique: satu perwakilan satu kali per menu
            $table->unique(['perwakilan_user_id', 'menu'], 'pkk_perwakilan_menu_unique');
        });
    }

    public function down(): void
    {
        Schema::table('p3k_pengumpulan_kolektif', function (Blueprint $table) {
            $table->dropForeign(['perwakilan_user_id']);
            $table->dropUnique('pkk_perwakilan_menu_unique');
            $table->dropColumn('menu');
            $table->unique('perwakilan_user_id');
            $table->foreign('perwakilan_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
