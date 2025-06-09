<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoketiWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'event_name',
        'webhook_url',
        'payload',
        'headers',
        'status',
        'attempts',
        'response_body',
        'response_status',
        'sent_at',
        'next_retry_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'attempts' => 'integer',
        'response_status' => 'integer',
        'sent_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    /**
     * Get the Soketi app that owns this webhook
     */
    public function soketiApp(): BelongsTo
    {
        return $this->belongsTo(SoketiApp::class, 'app_id', 'app_id');
    }

    /**
     * Scope for pending webhooks
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for sent webhooks
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for failed webhooks
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Mark webhook as sent
     */
    public function markAsSent(int $responseStatus, ?string $responseBody = null): void
    {
        $this->update([
            'status' => 'sent',
            'response_status' => $responseStatus,
            'response_body' => $responseBody,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark webhook as failed
     */
    public function markAsFailed(int $responseStatus, ?string $responseBody = null): void
    {
        $this->update([
            'status' => 'failed',
            'response_status' => $responseStatus,
            'response_body' => $responseBody,
            'attempts' => $this->attempts + 1,
            'next_retry_at' => now()->addMinutes(pow(2, $this->attempts)), // Exponential backoff
        ]);
    }

    /**
     * Check if webhook should be retried
     */
    public function shouldRetry(): bool
    {
        return $this->status === 'failed'
            && $this->attempts < 5
            && ($this->next_retry_at === null || $this->next_retry_at->isPast());
    }
}
