<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P3kPengumpulanKolektifItem extends Model
{
    use HasFactory;

    protected $table = 'p3k_pengumpulan_kolektif_item';

    protected $fillable = [
        'pengumpulan_kolektif_id',
        'p3k_barang_kebutuhan_id',
        'jumlah_dibawa',
    ];

    public function pengumpulan()
    {
        return $this->belongsTo(P3kPengumpulanKolektif::class, 'pengumpulan_kolektif_id');
    }

    public function barang()
    {
        return $this->belongsTo(P3kBarangKebutuhan::class, 'p3k_barang_kebutuhan_id');
    }
}
