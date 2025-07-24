<?php

namespace App\Jobs;

use App\Models\SoketiWebhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessSoketiWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $backoff = 60; // Base backoff in seconds

    protected SoketiWebhook $webhook;

    public function __construct(SoketiWebhook $webhook)
    {
        $this->webhook = $webhook;
    }

    public function handle(): void
    {
        try {
            $headers = array_merge([
                'Content-Type' => 'application/json',
                'User-Agent' => 'Soketi-Dashboard/1.0',
                'X-Soketi-Signature' => $this->generateSignature(),
            ], $this->webhook->headers ?? []);

            $response = Http::withHeaders($headers)
                ->timeout(config('soketi.webhooks.timeout', 30))
                ->post($this->webhook->webhook_url, $this->webhook->payload ?? []);

            if ($response->successful()) {
                $this->webhook->markAsSent($response->status(), $response->body());

                Log::info('Webhook sent successfully', [
                    'webhook_id' => $this->webhook->id,
                    'app_id' => $this->webhook->app_id,
                    'url' => $this->webhook->webhook_url,
                    'status' => $response->status(),
                ]);
            } else {
                throw new \Exception("HTTP {$response->status()}: {$response->body()}");
            }

        } catch (\Exception $e) {
            $this->webhook->markAsFailed(
                $response->status() ?? 0,
                $e->getMessage()
            );

            Log::warning('Webhook failed', [
                'webhook_id' => $this->webhook->id,
                'app_id' => $this->webhook->app_id,
                'url' => $this->webhook->webhook_url,
                'error' => $e->getMessage(),
                'attempt' => $this->webhook->attempts,
            ]);

            // Re-queue if we should retry
            if ($this->webhook->shouldRetry()) {
                $delay = now()->addMinutes(
                    pow(2, $this->webhook->attempts) * config('soketi.webhooks.retry_delay_base', 2)
                );

                static::dispatch($this->webhook)->delay($delay);
            }
        }
    }

    protected function generateSignature(): string
    {
        $payload = json_encode($this->webhook->payload ?? []);
        $secret = $this->webhook->soketiApp->secret ?? '';

        return hash_hmac('sha256', $payload, $secret);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Webhook job failed permanently', [
            'webhook_id' => $this->webhook->id,
            'app_id' => $this->webhook->app_id,
            'error' => $exception->getMessage(),
        ]);

        $this->webhook->markAsFailed(0, 'Job failed: ' . $exception->getMessage());
    }
}
