<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiPasswordHistory extends Model
{
    protected $table = 'absensi_password_history';

    protected $fillable = [
        'tanggal',
        'password_tampil',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}