<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AbsensiPanitia extends Model
{
    protected $table = 'absensi_panitia';
    protected $fillable = [
        'user_id', 'qr_session_id', 'nama', 'nim', 
        'divisi', 'status', 'ip_address', 'waktu_absen'
    ];
    protected $casts = ['waktu_absen' => 'datetime'];
    
    public function user() { return $this->belongsTo(User::class); }
    public function qrSession() { return $this->belongsTo(QrSession::class); }
}