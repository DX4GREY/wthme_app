<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P3kPengumpulanIndividu extends Model
{
    use HasFactory;

    protected $table = 'p3k_pengumpulan_individu';

    protected $fillable = [
        'p3k_barang_kebutuhan_id',
        'user_id',
        'jumlah_dibawa',
        'foto_bukti',
        'is_validated',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_validated' => 'boolean',
        ];
    }

    public function barang()
    {
        return $this->belongsTo(P3kBarangKebutuhan::class, 'p3k_barang_kebutuhan_id');
    }

    public function peserta()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
