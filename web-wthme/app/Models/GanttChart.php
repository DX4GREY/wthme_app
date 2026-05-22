<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GanttChart extends Model
{
    // Tambahkan ini:
    protected $fillable = [
        'nama_kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'deskripsi',
    ];
}