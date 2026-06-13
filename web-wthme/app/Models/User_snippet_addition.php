<?php
// ─────────────────────────────────────────────────────────────
// TAMBAHKAN snippet berikut ke dalam class User di app/Models/User.php
// (di antara isMentor() / isAcara() dan akhir class)
// ─────────────────────────────────────────────────────────────

    // Cek apakah user adalah panitia divisi P3K
    public function isP3k(): bool
    {
        return strtoupper($this->divisi ?? '') === 'P3K' || $this->role === 'admin';
    }

    // Relasi: kelompok-kelompok yang menjadi tanggung jawab PJ P3K ini
    public function kelompokP3kBinaan()
    {
        return \App\Models\P3kPjKelompok::where('pj_p3k_id', $this->id)->pluck('kelompok')->toArray();
    }

    public function p3kPengumpulanDiupdate()
    {
        return $this->hasMany(\App\Models\P3kPengumpulanBarang::class, 'updated_by');
    }
