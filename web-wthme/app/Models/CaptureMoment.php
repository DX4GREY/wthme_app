<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaptureMoment extends Model
{
    protected $fillable = [
        'kelompok',
        'foto_path',
        'caption',
        'uploaded_by',
        'skor_kelengkapan',
        'skor_tema',
        'skor_estetika',
        'total_skor',
        'dinilai_oleh',
        'dinilai_at',
        'juara',
        'poin',
        'status',
    ];
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    protected function casts(): array
    {
        return [
            'dinilai_at' => 'datetime',
        ];
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'dinilai_oleh');
    }

    public function reactions()
    {
        return $this->hasMany(CaptureMomentReaction::class);
    }

    public function comments()
    {
        return $this->hasMany(CaptureMomentComment::class)->orderBy('created_at', 'asc');
    }

    public function sudahDinilai(): bool
    {
        return !is_null($this->total_skor);
    }

    // Helper label juara untuk tampilan badge
    public function labelJuara(): ?string
    {
        if (!$this->juara) return null;

        return match (true) {
            $this->juara === 1 => '🥇 Juara 1',
            $this->juara === 2 => '🥈 Juara 2',
            $this->juara === 3 => '🥉 Juara 3',
            default             => 'Juara ' . $this->juara,
        };
    }
}
