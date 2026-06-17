<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P3kPengumpulanKolektifAnggota extends Model
{
    use HasFactory;

    protected $table = 'p3k_pengumpulan_kolektif_anggota';

    protected $fillable = [
        'pengumpulan_kolektif_id',
        'user_id',
    ];

    public function pengumpulan()
    {
        return $this->belongsTo(P3kPengumpulanKolektif::class, 'pengumpulan_kolektif_id');
    }

    public function peserta()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
