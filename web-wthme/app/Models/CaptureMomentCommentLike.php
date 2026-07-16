<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaptureMomentCommentLike extends Model
{
    protected $fillable = [
        'capture_moment_comment_id',
        'user_id',
    ];

    public function comment()
    {
        return $this->belongsTo(CaptureMomentComment::class, 'capture_moment_comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}