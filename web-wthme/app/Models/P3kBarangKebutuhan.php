<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P3kBarangKebutuhan extends Model
{
    use HasFactory;

    protected $table = 'p3k_barang_kebutuhan';

    protected $fillable = [
        'nama_barang',
        'kategori',       // 'kelompok' | 'individu'
        'jumlah_kebutuhan',
        'satuan',
        'keterangan',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function pengumpulan()
    {
        return $this->hasMany(P3kPengumpulanBarang::class, 'p3k_barang_kebutuhan_id');
    }

    public function scopeKelompok($query)
    {
        return $query->where('kategori', 'kelompok');
    }

    public function scopeIndividu($query)
    {
        return $query->where('kategori', 'individu');
    }
}
