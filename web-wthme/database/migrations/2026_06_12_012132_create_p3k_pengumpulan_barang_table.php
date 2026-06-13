<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p3k_pengumpulan_barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p3k_barang_kebutuhan_id')->constrained('p3k_barang_kebutuhan')->onDelete('cascade');
            $table->string('kelompok');
            $table->integer('jumlah_terkumpul')->default(0);

            // Khusus barang individu: tracking pemakaian selama acara
            $table->integer('jumlah_terpakai')->default(0);

            $table->string('foto_bukti')->nullable();
            $table->boolean('is_validated')->default(false);

            $table->foreignId('pj_p3k_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['p3k_barang_kebutuhan_id', 'kelompok']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p3k_pengumpulan_barang');
    }
};