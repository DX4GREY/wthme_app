<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformasiPeserta extends Model
{
    // Hanya kolom ini yang boleh diisi mass-assignment
    protected $fillable = [
        'judul',
        'url_link',
        'kategori',
        'konten',
    ];
}
