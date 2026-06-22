<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class P3kStokGlobal extends Model
{
    protected $table = 'p3k_stok_global';

    protected $fillable = [
        'p3k_barang_kebutuhan_id',
        'total_terpakai',
        'updated_by',
    ];

    public function barang()
    {
        return $this->belongsTo(P3kBarangKebutuhan::class, 'p3k_barang_kebutuhan_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
