<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('p3k_pengumpulan_individu', function (Blueprint $table) {
            $table->dropColumn('jumlah_terpakai');
        });
    }

    public function down(): void
    {
        Schema::table('p3k_pengumpulan_individu', function (Blueprint $table) {
            $table->integer('jumlah_terpakai')->default(0);
        });
    }
};
