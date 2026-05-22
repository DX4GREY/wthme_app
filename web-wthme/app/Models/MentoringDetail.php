<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentoringDetail extends Model
{
    protected $fillable = ['mentoring_id', 'peserta_id', 'kehadiran', 'keterangan'];

    public function peserta()
    {
        return $this->belongsTo(User::class, 'peserta_id');
    }
    
    public function mentoring()
    {
        return $this->belongsTo(Mentoring::class);
    }
}
