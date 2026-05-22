<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_transaksi', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->unsignedBigInteger('nominal');
            $table->string('divisi')->nullable();        // wajib saat keluar
            $table->text('keterangan');
            $table->string('pic');                       // penanggung jawab
            $table->string('bukti_file')->nullable();    // path file upload
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_transaksi');
    }
};
