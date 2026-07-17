<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaptureMomentComment extends Model
{
    protected $fillable = [
        'capture_moment_id',
        'user_id',
        'comment',
    ];

    public function captureMoment()
    {
        return $this->belongsTo(CaptureMoment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(CaptureMomentCommentLike::class);
    }

    // Helper: jumlah likes
    public function likesCount(): int
    {
        return $this->likes()->count();
    }
}