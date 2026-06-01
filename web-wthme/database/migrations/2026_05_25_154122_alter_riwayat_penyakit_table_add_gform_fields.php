<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_penyakit', function (Blueprint $table) {
            // Menambahkan kolom baru pasca struktur dasar lama
            $table->string('no_telp')->nullable()->after('kelompok');
            $table->string('no_telp_ortu')->nullable()->after('no_telp');
            $table->text('alamat_rumah')->nullable()->after('no_telp_ortu');
            $table->text('riwayat_cedera')->nullable()->after('obat_rutin');
            $table->text('alergi_makanan')->nullable()->after('riwayat_cedera');
            $table->string('bukti_kesehatan')->nullable()->after('keterangan_tambahan');
            
            // Mengubah tipe kolom lama ke text agar muat deskripsi panjang ala GForm
            $table->text('riwayat_penyakit')->nullable()->change();
            $table->text('alergi')->nullable()->change();
            $table->text('obat_rutin')->nullable()->change();
            $table->text('keterangan_tambahan')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_penyakit', function (Blueprint $table) {
            $table->dropColumn([
                'no_telp', 
                'no_telp_ortu', 
                'alamat_rumah', 
                'riwayat_cedera', 
                'alergi_makanan', 
                'bukti_kesehatan'
            ]);
        });
    }
};