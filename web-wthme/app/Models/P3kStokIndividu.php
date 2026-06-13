<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P3kStokIndividu extends Model
{
    use HasFactory;

    protected $table = 'p3k_stok_individu';

    protected $fillable = [
        'p3k_barang_kebutuhan_id',
        'total_terkumpul',
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

    public function getTotalSisaAttribute(): int
    {
        return max(0, $this->total_terkumpul - $this->total_terpakai);
    }

    // Recalculate total_terkumpul = SUM(jumlah_dibawa) dari semua peserta untuk barang ini
    public static function recalcTerkumpul($barangId): self
    {
        $sum = P3kPengumpulanIndividu::where('p3k_barang_kebutuhan_id', $barangId)->sum('jumlah_dibawa');

        $stok = static::firstOrCreate(['p3k_barang_kebutuhan_id' => $barangId]);
        $stok->total_terkumpul = $sum;

        // total_terpakai tidak boleh melebihi total_terkumpul setelah recalc
        if ($stok->total_terpakai > $sum) {
            $stok->total_terpakai = $sum;
        }

        $stok->save();

        return $stok;
    }
}
