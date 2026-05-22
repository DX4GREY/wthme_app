<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkResource extends Model
{
    protected $fillable = ['nama', 'url', 'ikon', 'untuk', 'urutan', 'aktif'];
}