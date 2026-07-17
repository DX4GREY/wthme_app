<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaptureMomentSetting extends Model
{
    protected $table = 'capture_moment_settings';

    protected $fillable = [
        'mulai_at',
        'selesai_at',
        'tema',
    ];

    protected function casts(): array
    {
        return [
            'mulai_at'   => 'datetime',
            'selesai_at' => 'datetime',
        ];
    }

    public static function current(): self
    {
        // Cari baris pertama, atau buat baru kalau belum ada
        $setting = self::first();
        
        if (!$setting) {
            $setting = self::create([
                'tema' => 'Kekeluargaan',
            ]);
        }
        
        return $setting;
    }

    public function sedangBerjalan(): bool
    {
        $now = now();

        if ($this->mulai_at && $now->lt($this->mulai_at)) {
            return false; // belum mulai
        }

        if ($this->selesai_at && $now->gt($this->selesai_at)) {
            return false; // sudah lewat deadline
        }

        return true;
    }

    public function statusLabel(): string
    {
        $now = now();

        if ($this->mulai_at && $now->lt($this->mulai_at)) {
            return 'Belum dibuka';
        }

        if ($this->selesai_at && $now->gt($this->selesai_at)) {
            return 'Sudah ditutup';
        }

        return 'Sedang berjalan';
    }
}