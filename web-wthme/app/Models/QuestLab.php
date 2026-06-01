<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestLab extends Model
{
    protected $fillable = ['user_id', 'nama_lab', 'foto_selfie', 'status', 'submitted_at'];

    protected $casts = [
        'submitted_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}