<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SoketiApp extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                do {
                    $id = str_pad(random_int(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
                } while (static::where('id', $id)->exists());

                $model->id = $id;
            }

            // Generate key and secret if not provided
            if (empty($model->key)) {
                $model->key = Str::random(20);
            }

            if (empty($model->secret)) {
                $model->secret = Str::random(40);
            }
        });
    }

    protected $fillable = [
        'id',
        'app_name',
        'app_description',
        'key',
        'secret',
        'max_connections',
        'enable_client_messages',
        'enabled',
        'max_backend_events_per_sec',
        'max_client_events_per_sec',
        'max_read_req_per_sec',
        'webhooks',
        'max_presence_members_per_channel',
        'max_presence_member_size_in_kb',
        'max_channel_name_length',
        'max_event_channels_at_once',
        'max_event_name_length',
        'max_event_payload_in_kb',
        'max_event_batch_size',
        'enable_user_authentication'
    ];

    protected $casts = [
        'max_connections' => 'integer',
        'enable_client_messages' => 'boolean',
        'enabled' => 'boolean',
        'max_backend_events_per_sec' => 'integer',
        'max_client_events_per_sec' => 'integer',
        'max_read_req_per_sec' => 'integer',
        'webhooks' => 'json',
        'max_presence_members_per_channel' => 'integer',
        'max_presence_member_size_in_kb' => 'integer',
        'max_channel_name_length' => 'integer',
        'max_event_channels_at_once' => 'integer',
        'max_event_name_length' => 'integer',
        'max_event_payload_in_kb' => 'integer',
        'max_event_batch_size' => 'integer',
        'enable_user_authentication' => 'boolean'
    ];

    protected $attributes = [
        'max_connections' => 1000,
        'enable_client_messages' => true,
        'enabled' => true,
        'max_backend_events_per_sec' => 100,
        'max_client_events_per_sec' => 100,
        'max_read_req_per_sec' => 100,
        'max_presence_members_per_channel' => 100,
        'max_presence_member_size_in_kb' => 2,
        'max_channel_name_length' => 200,
        'max_event_channels_at_once' => 100,
        'max_event_name_length' => 200,
        'max_event_payload_in_kb' => 100,
        'max_event_batch_size' => 10,
        'enable_user_authentication' => false
    ];

    // Relationships
    public function connections(): HasMany
    {
        return $this->hasMany(SoketiConnection::class, 'app_id', 'id');
    }

    public function debugEvents(): HasMany
    {
        return $this->hasMany(SoketiDebugEvent::class, 'app_id', 'id');
    }

    public function webhookEvents(): HasMany
    {
        return $this->hasMany(SoketiWebhook::class, 'app_id', 'id');
    }

    // Utility Methods
    public function getCurrentConnectionCount(): int
    {
        return $this->connections()->where('is_connected', true)->count();
    }

    public function getConnectionUtilization(): float
    {
        if ($this->max_connections <= 0) {
            return 0;
        }

        return round(($this->getCurrentConnectionCount() / $this->max_connections) * 100, 2);
    }

    public function isNearCapacity(int $threshold = 80): bool
    {
        return $this->getConnectionUtilization() >= $threshold;
    }

    public function getActiveChannels(): array
    {
        return $this->connections()
            ->where('is_connected', true)
            ->whereNotNull('channel_name')
            ->distinct('channel_name')
            ->pluck('channel_name')
            ->toArray();
    }

    public function getTotalEventsToday(): int
    {
        return $this->debugEvents()
            ->whereDate('timestamp', today())
            ->count();
    }

    /**
     * Scope to get only enabled apps
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope to get apps with client messages enabled
     */
    public function scopeWithClientMessages($query)
    {
        return $query->where('enable_client_messages', true);
    }

    public function getAverageConnectionDuration(): ?float
    {
        $avgDuration = $this->connections()
            ->whereNotNull('connected_at')
            ->whereNotNull('disconnected_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, connected_at, disconnected_at)) as avg_duration')
            ->value('avg_duration');

        return $avgDuration ? round($avgDuration, 2) : null;
    }

    public function regenerateCredentials(): void
    {
        $this->update([
            'key' => Str::random(20),
            'secret' => Str::random(40),
        ]);
    }
    public function scopeDisabled($query)
    {
        return $query->where('enabled', false);
    }

    public function scopeNearCapacity($query, int $threshold = 80)
    {
        return $query->whereRaw('
            (SELECT COUNT(*) FROM soketi_connections
             WHERE app_id = soketi_apps.id AND is_connected = 1) / max_connections * 100 >= ?',
            [$threshold]
        );
    }
}
