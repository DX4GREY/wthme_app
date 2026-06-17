<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P3kPengumpulanKolektif extends Model
{
    use HasFactory;

    protected $table = 'p3k_pengumpulan_kolektif';

    protected $fillable = [
        'perwakilan_user_id',
        'kelompok',
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

    public function perwakilan()
    {
        return $this->belongsTo(User::class, 'perwakilan_user_id');
    }

    public function anggota()
    {
        return $this->hasMany(P3kPengumpulanKolektifAnggota::class, 'pengumpulan_kolektif_id');
    }

    public function items()
    {
        return $this->hasMany(P3kPengumpulanKolektifItem::class, 'pengumpulan_kolektif_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Jumlah anggota yang tercakup di pengumpulan ini (termasuk perwakilan sendiri).
     * Pakai withCount('anggota') di query untuk hindari query tambahan saat dipakai berulang.
     */
    public function getJumlahAnggotaAttribute(): int
    {
        if (array_key_exists('anggota_count', $this->attributes)) {
            return (int) $this->attributes['anggota_count'];
        }

        return $this->anggota()->count();
    }

    /**
     * Target untuk satu barang individu dalam pengumpulan ini.
     * target = jumlah_kebutuhan (per orang) x jumlah anggota yang tercakup (termasuk perwakilan).
     */
    public function targetUntuk(P3kBarangKebutuhan $barang): int
    {
        return $barang->jumlah_kebutuhan * $this->jumlah_anggota;
    }

    public function jumlahDibawaUntuk(int $barangId): int
    {
        $item = $this->items->firstWhere('p3k_barang_kebutuhan_id', $barangId);
        return $item ? $item->jumlah_dibawa : 0;
    }
}
