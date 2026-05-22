<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TugasPengumpulan extends Model
{
    protected $table = 'tugas_pengumpulan';

    protected $fillable = [
        'user_id', 'tugas_kategori_id', 'nama', 'nim', 'kelompok',
        'file_path', 'file_nama_asli', 'file_ekstensi', 'file_ukuran',
        'status', 'catatan', 'dikumpulkan_at',
    ];

    protected $casts = [
        'dikumpulkan_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tugasKategori()
    {
        return $this->belongsTo(TugasKategori::class);
    }

    /** Ukuran file dalam format manusiawi */
    public function ukuranFormatted(): string
    {
        $kb = $this->file_ukuran / 1024;
        if ($kb < 1024) return round($kb, 1) . ' KB';
        return round($kb / 1024, 1) . ' MB';
    }
}
