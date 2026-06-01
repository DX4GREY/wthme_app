<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoKekeluargaan extends Model
{
    protected $fillable = ['pengirim_id', 'teman_id', 'foto', 'status'];

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function teman()
    {
        return $this->belongsTo(User::class, 'teman_id');
    }
}