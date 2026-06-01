<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoinKeaktifan extends Model
{
    // Mengarahkan model ke tabel yang Anda buat di migrasi
    protected $table = 'poin_keaktifan';

    protected $fillable = [
        'peserta_id', 
        'panitia_id', 
        'poin', 
        'keterangan'
    ];

    // Relasi ke panitia yang memberi nilai
    public function panitia()
    {
        return $this->belongsTo(User::class, 'panitia_id');
    }

    // Relasi ke peserta yang menerima nilai
    public function peserta()
    {
        return $this->belongsTo(User::class, 'peserta_id');
    }
}