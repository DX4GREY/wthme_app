<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mentoring extends Model
{
    protected $fillable = ['mentor_id', 'nama_kegiatan', 'kelompok', 'tanggal'];

    public function details()
    {
        return $this->hasMany(MentoringDetail::class);
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
}
