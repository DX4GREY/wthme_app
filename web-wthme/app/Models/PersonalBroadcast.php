<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalBroadcast extends Model
{
    protected $fillable = [
        'judul',
        'konten',
        'created_by',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(PersonalBroadcastRecipient::class);
    }

    public function unreadRecipients(): HasMany
    {
        return $this->hasMany(PersonalBroadcastRecipient::class)->whereNull('viewed_at');
    }
}
