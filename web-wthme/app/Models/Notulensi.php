<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notulensi extends Model
{
    use HasFactory;

    // Nama tabel secara eksplisit
    protected $table = 'notulensi';

    // Kolom yang boleh diisi (Mass Assignable)
    protected $fillable = [
        'topik',
        'tanggal',
        'tempat',
        'pemimpin_rapat',
        'created_by',
    ];

    /**
     * Casting tipe data kolom
     */
    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Relasi ke Poin Notulensi (Satu rapat punya banyak poin)
     */
    public function poin(): HasMany
    {
        return $this->hasMany(NotulensiPoin::class, 'notulensi_id');
    }

    /**
     * Relasi ke User (Siapa sekretaris/panitia yang membuat)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}