<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AbsensiPeserta extends Model
{
    protected $table = 'absensi_peserta';
    protected $fillable = [
        'user_id', 'qr_session_id', 'nama', 'nim', 
        'angkatan', 'kelompok', 'status', 'ip_address', 'waktu_absen', 'metode'
    ];
    protected $casts = ['waktu_absen' => 'datetime'];
    
    public function user() { return $this->belongsTo(User::class); }
    public function qrSession() { return $this->belongsTo(QrSession::class); }
}