<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

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

    // Relationships
    public function soketiApp(): BelongsTo
    {
        return $this->belongsTo(SoketiApp::class, 'app_id', 'id');
    }

    // Scopes
    public function scopeConnected($query)
    {
        return $query->where('is_connected', true);
    }

    public function scopeDisconnected($query)
    {
        return $query->where('is_connected', false);
    }

    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel_name', $channel);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->whereJsonContains('user_data->user_id', $userId);
    }

    public function scopeActiveAt($query, Carbon $dateTime)
    {
        return $query->where('connected_at', '<=', $dateTime)
            ->where(function ($q) use ($dateTime) {
                $q->whereNull('disconnected_at')
                    ->orWhere('disconnected_at', '>', $dateTime);
            });
    }

    // Utility Methods
    public function getDurationAttribute(): ?int
    {
        if (!$this->connected_at) {
            return null;
        }

        $endTime = $this->disconnected_at ?? now();
        return $this->connected_at->diffInSeconds($endTime);
    }

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

    public function getUserId(): ?string
    {
        return $this->user_data['user_id'] ?? null;
    }

    public function getLocation(): ?string
    {
        return $this->user_data['location'] ?? null;
    }

    public function getBrowser(): ?string
    {
        if (!$this->user_agent) {
            return null;
        }

        // Simple browser detection
        if (str_contains($this->user_agent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($this->user_agent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($this->user_agent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($this->user_agent, 'Edge')) {
            return 'Edge';
        }

        return 'Unknown';
    }

    public function disconnect(): void
    {
        $this->update([
            'is_connected' => false,
            'disconnected_at' => now(),
        ]);
    }

    public function isLongRunning(int $hours = 24): bool
    {
        if (!$this->connected_at || !$this->is_connected) {
            return false;
        }

        return $this->connected_at->diffInHours(now()) >= $hours;
    }
}
