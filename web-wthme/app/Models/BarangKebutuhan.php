<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangKebutuhan extends Model
{
    protected $table = 'barang_kebutuhan';

    protected $fillable = [
        'nama_barang',
        'jumlah_kebutuhan',
        'satuan',
        'keterangan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function pengumpulan()
    {
        return $this->hasMany(PengumpulanBarang::class, 'barang_kebutuhan_id');
    }

    public function pengumpulanKelompok(int $kelompok)
    {
        return $this->pengumpulan()->where('kelompok', $kelompok)->first();
    }
}