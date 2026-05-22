<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengumpulanBarang extends Model
{
    protected $table = 'pengumpulan_barang';

    protected $fillable = [
        'barang_kebutuhan_id',
        'kelompok',
        'jumlah_terkumpul',
        'foto_bukti',
        'updated_by',
    ];

    public function barang()
    {
        return $this->belongsTo(BarangKebutuhan::class, 'barang_kebutuhan_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isLengkap(): bool
    {
        return $this->jumlah_terkumpul >= $this->barang->jumlah_kebutuhan;
    }
}