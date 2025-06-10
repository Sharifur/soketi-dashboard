<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoketiDebugEvent extends Model
{
    protected $fillable = [
        'app_id',
        'event_type',
        'channel',
        'event_name',
        'payload',
        'socket_id',
        'user_id',
        'timestamp',
    ];

    protected $casts = [
        'payload' => 'json',
        'timestamp' => 'datetime',
    ];

    public function soketiApp(): BelongsTo
    {
        return $this->belongsTo(SoketiApp::class, 'app_id', 'id');
    }
}
