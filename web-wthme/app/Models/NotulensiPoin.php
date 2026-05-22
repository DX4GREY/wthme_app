<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotulensiPoin extends Model
{
    use HasFactory;

    // Nama tabel secara eksplisit
    protected $table = 'notulensi_poin';

    // Kolom yang boleh diisi
    protected $fillable = [
        'notulensi_id',
        'divisi',
        'isi_poin',
    ];

    /**
     * Relasi balik ke Notulensi (Setiap poin dimiliki oleh satu rapat)
     */
    public function notulensi(): BelongsTo
    {
        return $this->belongsTo(Notulensi::class, 'notulensi_id');
    }
}