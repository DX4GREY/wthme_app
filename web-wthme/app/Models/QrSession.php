<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrSession extends Model
{


    public function pembuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function absensiPeserta()
    {
        return $this->hasMany(AbsensiPeserta::class);
    }

    public function absensiPanitia()
    {
        return $this->hasMany(AbsensiPanitia::class);
    }

    protected $fillable = [
    'session_code', 'nama_sesi', 'untuk',
    'aktif', 'berlaku_hingga', 'dibuat_oleh',
    'rotating', 'rotate_interval', 'current_token', 'token_expires_at',
    ];

    protected $casts = [
        'berlaku_hingga'   => 'datetime',
        'token_expires_at' => 'datetime',
        'aktif'            => 'boolean',
        'rotating'         => 'boolean',
        'rotate_interval'  => 'integer',
    ];

    // Generate token baru
    public function regenerateToken(): string
    {
        $token = \Illuminate\Support\Str::random(32);
        $this->update([
            'current_token'   => $token,
            'token_expires_at'=> now()->addSeconds($this->rotate_interval),
        ]);
        return $token;
    }

    // Cek apakah token masih valid
    public function isTokenValid(string $token): bool
    {
        if (!$this->rotating) return true; // kalau tidak rotating, skip cek token
        return $this->current_token === $token
            && $this->token_expires_at
            && now()->isBefore($this->token_expires_at);
    }
}