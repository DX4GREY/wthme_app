<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestMeet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kategori_angkatan',
        'tipe_meet',
        'selected_abang',
        'foto_bukti',
        'status',
        'validated_by'
    ];

    protected $casts = [
        'selected_abang' => 'array', // Otomatis konversi JSON string ke PHP Array
    ];

    public function peserta()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}