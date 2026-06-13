<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P3kPjKelompok extends Model
{
    use HasFactory;

    protected $table = 'p3k_pj_kelompok';

    protected $fillable = [
        'kelompok',
        'pj_p3k_id',
    ];

    public function pj()
    {
        return $this->belongsTo(User::class, 'pj_p3k_id');
    }

    public static function pjUntukKelompok($kelompok)
    {
        $row = static::where('kelompok', $kelompok)->first();
        return $row ? $row->pj_p3k_id : null;
    }

    // Daftar kelompok yang jadi tanggung jawab seorang PJ P3K
    public static function kelompokUntukPj($userId)
    {
        return static::where('pj_p3k_id', $userId)->pluck('kelompok')->toArray();
    }
}
