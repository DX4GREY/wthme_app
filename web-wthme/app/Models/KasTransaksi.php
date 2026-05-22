<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasTransaksi extends Model
{
    protected $table = 'kas_transaksi';

    protected $fillable = [
        'tanggal', 'jenis', 'nominal', 'divisi',
        'keterangan', 'pic', 'bukti_file', 'created_by',
    ];

    protected $casts = [
        'tanggal'  => 'date',
        'nominal'  => 'integer',
    ];

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
