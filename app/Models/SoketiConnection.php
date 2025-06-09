<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoketiConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'connection_id',
        'app_id',
        'socket_id',
        'channel_name',
        'user_data',
        'ip_address',
        'user_agent',
        'is_connected',
        'connected_at',
        'disconnected_at',
    ];

    protected $casts = [
        'user_data' => 'array',
        'is_connected' => 'boolean',
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
    ];

    /**
     * Get the Soketi app that owns this connection
     */
    public function soketiApp(): BelongsTo
    {
        return $this->belongsTo(SoketiApp::class, 'app_id', 'app_id');
    }

    /**
     * Scope for active connections only
     */
    public function scopeConnected($query)
    {
        return $query->where('is_connected', true);
    }

    /**
     * Scope for disconnected connections only
     */
    public function scopeDisconnected($query)
    {
        return $query->where('is_connected', false);
    }

    /**
     * Get connection duration
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->connected_at) {
            return null;
        }

        $endTime = $this->disconnected_at ?? now();
        return $this->connected_at->diffInSeconds($endTime);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $duration = $this->duration;

        if ($duration === null) {
            return 'Unknown';
        }

        if ($duration < 60) {
            return $duration . 's';
        }

        if ($duration < 3600) {
            return round($duration / 60, 1) . 'm';
        }

        return round($duration / 3600, 1) . 'h';
    }
}
