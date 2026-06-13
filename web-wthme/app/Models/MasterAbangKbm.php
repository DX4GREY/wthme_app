<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterAbangKbm extends Model
{
    use HasFactory;

    protected $table = 'master_abang_kbms';
    
    protected $fillable = [
        'name',
        'angkatan',
    ];
}