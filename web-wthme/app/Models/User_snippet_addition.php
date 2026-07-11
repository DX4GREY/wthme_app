<?php
namespace App\Models;

// Trait yang berisi snippet untuk ditambahkan ke dalam class User
trait UserSnippetAddition
{
    // Cek apakah user adalah panitia divisi P3K
    public function isP3k(): bool
    {
        return strtoupper($this->divisi ?? '') === 'P3K' || $this->role === 'admin';
    }

    // Relasi: kelompok-kelompok yang menjadi tanggung jawab PJ P3K ini
    public function kelompokP3kBinaan()
    {
        return P3kPjKelompok::where('pj_p3k_id', $this->id)->pluck('kelompok')->toArray();
    }

    public function p3kPengumpulanDiupdate()
    {
        return $this->hasMany(P3kPengumpulanBarang::class, 'updated_by');
    }
}
