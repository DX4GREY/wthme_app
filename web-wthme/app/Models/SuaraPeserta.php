<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuaraPeserta extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pesan',
        'foto',
        'anonim',
        'dibaca',
        'dibaca_at',
    ];

    protected $casts = [
        'anonim' => 'boolean',
        'dibaca' => 'boolean',
        'dibaca_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reads()
    {
        return $this->hasMany(SuaraPesertaRead::class);
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'suara_peserta_reads')
            ->withTimestamps();
    }
}
