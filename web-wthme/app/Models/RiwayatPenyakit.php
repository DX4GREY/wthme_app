<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPenyakit extends Model
{
    protected $table = 'riwayat_penyakit';
    protected $fillable = [
        'user_id',
        'nama',
        'nim',
        'kelompok',
        'no_telp',
        'no_telp_ortu',
        'alamat_rumah',
        'kondisi_kesehatan',
        'riwayat_penyakit',
        'alergi',
        'obat_rutin',
        'riwayat_cedera',
        'alergi_makanan',
        'keterangan_tambahan',
        'bukti_kesehatan',
        'warna_pita'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
