<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaptureMomentReaction extends Model
{
    protected $fillable = [
        'capture_moment_id',
        'user_id',
        'emoji',
    ];

    public function captureMoment()
    {
        return $this->belongsTo(CaptureMoment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
