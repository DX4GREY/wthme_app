<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class DailyAbsensiPassword extends Model
{
    protected $table = 'daily_absensi_passwords';

    protected $fillable = [
        'tanggal',
        'password',
        'password_tampil',
        'dibuat_oleh',
        'dibuat_pada',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'dibuat_pada' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($password) {
            // Hash password sebelum disimpan
            $password->password = bcrypt($password->password);
        });

        static::updating(function ($password) {
            // Hash password sebelum update
            $password->password = bcrypt($password->password);
        });
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function scopeForDate(Builder $query, $date)
    {
        return $query->where('tanggal', $date);
    }

    public static function getTodayPassword()
    {
        return static::where('tanggal', date('Y-m-d'))->first();
    }
}