<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SoketiApp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'app_id',
        'app_key',
        'app_secret',
        'max_connections',
        'enable_client_messages',
        'enable_statistics',
        'enable_webhooks',
        'webhook_urls',
        'webhook_headers',
        'webhook_events',
        'is_active',
        'description',
        'user_id',
    ];

    protected $casts = [
        'webhook_urls' => 'array',
        'webhook_headers' => 'array',
        'webhook_events' => 'array',
        'enable_client_messages' => 'boolean',
        'enable_statistics' => 'boolean',
        'enable_webhooks' => 'boolean',
        'is_active' => 'boolean',
        'max_connections' => 'integer',
    ];

    protected $hidden = [
        'app_secret',
    ];

    /**
     * Get the connections for this app
     */
    public function connections(): HasMany
    {
        return $this->hasMany(SoketiConnection::class, 'app_id', 'app_id');
    }

    /**
     * Get the webhooks for this app
     */
    public function webhooks(): HasMany
    {
        return $this->hasMany(SoketiWebhook::class, 'app_id', 'app_id');
    }

    /**
     * Get the user that owns this app
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a unique app ID
     */
    public static function generateAppId(): string
    {
        do {
            $appId = 'app-' . Str::random(8);
        } while (self::where('app_id', $appId)->exists());

        return $appId;
    }

    /**
     * Generate a unique app key
     */
    public static function generateAppKey(): string
    {
        do {
            $appKey = Str::random(20);
        } while (self::where('app_key', $appKey)->exists());

        return $appKey;
    }

    /**
     * Generate a unique app secret
     */
    public static function generateAppSecret(): string
    {
        return Str::random(40);
    }

    /**
     * Get current connection count for this app
     */
    public function getCurrentConnectionCount(): int
    {
        return $this->connections()
            ->where('is_connected', true)
            ->count();
    }

    /**
     * Check if app has reached max connections
     */
    public function hasReachedMaxConnections(): bool
    {
        if ($this->max_connections <= 0) {
            return false; // Unlimited connections
        }

        return $this->getCurrentConnectionCount() >= $this->max_connections;
    }

    /**
     * Get app status
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->hasReachedMaxConnections()) {
            return 'at_limit';
        }

        return 'active';
    }

    /**
     * Scope for active apps only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->app_id)) {
                $model->app_id = self::generateAppId();
            }
            if (empty($model->app_key)) {
                $model->app_key = self::generateAppKey();
            }
            if (empty($model->app_secret)) {
                $model->app_secret = self::generateAppSecret();
            }
        });
    }
}
