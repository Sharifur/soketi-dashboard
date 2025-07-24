<?php

namespace App\Jobs;

use App\Services\SoketiStatsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SoketiCleanupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $connectionRetentionDays;
    protected int $eventRetentionDays;

    public function __construct(int $connectionRetentionDays = 7, int $eventRetentionDays = 30)
    {
        $this->connectionRetentionDays = $connectionRetentionDays;
        $this->eventRetentionDays = $eventRetentionDays;
    }

    public function handle(SoketiStatsService $statsService): void
    {
        Log::info('Starting Soketi cleanup job', [
            'connection_retention_days' => $this->connectionRetentionDays,
            'event_retention_days' => $this->eventRetentionDays,
        ]);

        // Cleanup connections
        $connectionResult = $statsService->cleanup($this->connectionRetentionDays);

        // Cleanup events separately if different retention period
        if ($this->eventRetentionDays !== $this->connectionRetentionDays) {
            $eventResult = $statsService->cleanup($this->eventRetentionDays);
            $connectionResult['deleted_events'] = $eventResult['deleted_events'];
        }

        Log::info('Soketi cleanup job completed', [
            'deleted_connections' => $connectionResult['deleted_connections'],
            'deleted_events' => $connectionResult['deleted_events'],
            'cutoff_date' => $connectionResult['cutoff_date'],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Soketi cleanup job failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
