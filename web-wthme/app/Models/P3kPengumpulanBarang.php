<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P3kPengumpulanBarang extends Model
{
    use HasFactory;

    protected $table = 'p3k_pengumpulan_barang';

    protected $fillable = [
        'p3k_barang_kebutuhan_id',
        'kelompok',
        'jumlah_terkumpul',
        'jumlah_terpakai',
        'foto_bukti',
        'is_validated',
        'pj_p3k_id',
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

    public function pj()
    {
        return $this->belongsTo(User::class, 'pj_p3k_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Sisa stok = terkumpul - terpakai (untuk barang individu)
    public function getJumlahSisaAttribute(): int
    {
        return max(0, $this->jumlah_terkumpul - $this->jumlah_terpakai);
    }
}
