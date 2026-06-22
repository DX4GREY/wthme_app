<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class P3kBarangKebutuhan extends Model
{
    use HasFactory;

    protected $table = 'p3k_barang_kebutuhan';

    protected $fillable = [
        'nama_barang',
        'tipe',        // 'kelompok' | 'individu'
        'menu',        // 'logistik' | 'konsumsi' | 'p3k'
        'jumlah_kebutuhan',
        'satuan',
        'keterangan',
        'aktif',
    ];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    // ── Scopes: tipe ────────────────────────────────────────────────────────

    public function scopeKelompok($query)
    {
        return $query->where('tipe', 'kelompok');
    }

    public function scopeIndividu($query)
    {
        return $query->where('tipe', 'individu');
    }

    // ── Scopes: menu ────────────────────────────────────────────────────────

    public function scopeMenu($query, string $menu)
    {
        return $query->where('menu', $menu);
    }

    public function scopeLogistik($query) { return $query->where('menu', 'logistik'); }
    public function scopeKonsumsi($query) { return $query->where('menu', 'konsumsi'); }
    public function scopeP3k($query)      { return $query->where('menu', 'p3k'); }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /** Label menu yang ramah tampil di view */
    public function getLabelMenuAttribute(): string
    {
        return match($this->menu) {
            'logistik' => 'Logistik',
            'konsumsi'  => 'Konsumsi',
            'p3k'       => 'P3K',
            default     => ucfirst($this->menu),
        };
    }

    // ── Relations ───────────────────────────────────────────────────────────

    public function pengumpulan()
    {
        return $this->hasMany(P3kPengumpulanBarang::class, 'p3k_barang_kebutuhan_id');
    }
}
