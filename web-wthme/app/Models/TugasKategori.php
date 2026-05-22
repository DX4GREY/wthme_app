<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TugasKategori extends Model
{
    protected $table = 'tugas_kategori';

    protected $fillable = [
        'nama_tugas', 'deskripsi', 'deadline',
        'aktif', 'tipe_file', 'maks_ukuran', 'urutan', 'dibuat_oleh', 'file_petunjuk',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'aktif'    => 'boolean',
    ];

    public function pengumpulan()
    {
        return $this->hasMany(TugasPengumpulan::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /** Apakah sudah melewati deadline */
    public function isTerlambat(): bool
    {
        return $this->deadline && now()->isAfter($this->deadline);
    }

    /** Ekstensi yang diizinkan berdasarkan tipe_file */
    public function ekstensiDiizinkan(): array
    {
        return match($this->tipe_file) {
            'pdf'    => ['pdf'],
            'gambar' => ['jpg', 'jpeg', 'png', 'webp'],
            default  => ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx', 'zip'],
        };
    }
}
