<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p3k_pengumpulan_individu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p3k_barang_kebutuhan_id')->constrained('p3k_barang_kebutuhan')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // peserta pemilik barang

            $table->integer('jumlah_dibawa')->default(0);   // jumlah yang dibawa peserta ini
            $table->integer('jumlah_terpakai')->default(0); // dipakai selama acara (misal soffell habis sebagian)

            $table->string('foto_bukti')->nullable();
            $table->boolean('is_validated')->default(false); // dicek PJ P3K saat pengumpulan

            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['p3k_barang_kebutuhan_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p3k_pengumpulan_individu');
    }
};
