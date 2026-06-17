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
        'kelompok',
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

    /**
     * Recalculate total_terkumpul untuk barang ini di kelompok tertentu.
     * total_terkumpul = SUM(jumlah_dibawa) dari semua pengumpulan kolektif
     * yang berasal dari kelompok tsb (lintas semua perwakilan kelompok itu).
     */
    public static function recalcTerkumpul($barangId, $kelompok): self
    {
        $sum = P3kPengumpulanKolektifItem::where('p3k_barang_kebutuhan_id', $barangId)
            ->whereHas('pengumpulan', function ($q) use ($kelompok) {
                $q->where('kelompok', $kelompok);
            })
            ->sum('jumlah_dibawa');

        $stok = static::firstOrCreate([
            'p3k_barang_kebutuhan_id' => $barangId,
            'kelompok'                => $kelompok,
        ]);

        $stok->total_terkumpul = $sum;

        // total_terpakai tidak boleh melebihi total_terkumpul setelah recalc
        if ($stok->total_terpakai > $sum) {
            $stok->total_terpakai = $sum;
        }

        $stok->save();

        return $stok;
    }

    /**
     * Ambil aggregat global (lintas semua kelompok) untuk satu barang.
     * Dipakai di halaman index untuk tampilan read-only.
     */
    public static function globalSummary($barangId): array
    {
        $rows = static::where('p3k_barang_kebutuhan_id', $barangId)->get();
        return [
            'total_terkumpul' => $rows->sum('total_terkumpul'),
            'total_terpakai'  => $rows->sum('total_terpakai'),
            'total_sisa'      => $rows->sum(fn($r) => max(0, $r->total_terkumpul - $r->total_terpakai)),
        ];
    }
}
