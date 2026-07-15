<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuaraPesertaRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'suara_peserta_id',
        'user_id',
    ];

    public function suaraPeserta()
    {
        return $this->belongsTo(SuaraPeserta::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}