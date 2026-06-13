<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P3kObatPribadi extends Model
{
    use HasFactory;

    protected $table = 'p3k_obat_pribadi';

    protected $fillable = [
        'user_id',
        'kelompok',
        'penyakit',
        'nama_obat',
        'catatan',
        'pj_p3k_id',
        'sudah_diserahkan',
        'foto_bukti',
    ];

    protected function casts(): array
    {
        return [
            'sudah_diserahkan' => 'boolean',
        ];
    }

    public function peserta()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pj()
    {
        return $this->belongsTo(User::class, 'pj_p3k_id');
    }
}
