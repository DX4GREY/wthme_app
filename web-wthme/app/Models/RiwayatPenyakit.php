<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RiwayatPenyakit extends Model
{
    protected $table = 'riwayat_penyakit';
    protected $fillable = [
        'user_id', 'nama', 'nim', 'kelompok',
        'riwayat_penyakit', 'alergi', 'obat_rutin',
        'kondisi_kesehatan', 'keterangan_tambahan'
    ];
    
    public function user() { return $this->belongsTo(User::class); }
}