<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('poin_keaktifan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained('users')->onDelete('cascade');

            // 💡 UBAH BARIS INI: Tambahkan ->nullable() sebelum ->constrained()
            $table->foreignId('panitia_id')->nullable()->constrained('users')->onDelete('cascade');

            $table->integer('poin');
            $table->string('keterangan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poin_keaktifan');
    }
};
