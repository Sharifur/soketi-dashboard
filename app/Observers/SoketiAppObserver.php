<?php

namespace App\Observers;

use App\Models\SoketiApp;
use App\Services\SoketiConfigService;
use Illuminate\Support\Facades\Log;

class SoketiAppObserver
{
    protected SoketiConfigService $configService;

    public function __construct(SoketiConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Handle the SoketiApp "created" event.
     */
    public function created(SoketiApp $soketiApp): void
    {
        Log::info('SoketiApp created');
        $this->configService->syncToConfig();
    }

    /**
     * Handle the SoketiApp "updated" event.
     */
    public function updated(SoketiApp $soketiApp): void
    {
        $this->configService->syncToConfig();
    }

    /**
     * Handle the SoketiApp "deleted" event.
     */
    public function deleted(SoketiApp $soketiApp): void
    {
        $this->configService->syncToConfig();
    }

    /**
     * Handle the SoketiApp "restored" event.
     */
    public function restored(SoketiApp $soketiApp): void
    {
        $this->configService->syncToConfig();
    }
}
