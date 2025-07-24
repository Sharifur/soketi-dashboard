<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SoketiAppsWidget extends Widget
{
    protected static string $view = 'filament.widgets.soketi-apps';
    protected static ?int $sort = 3;

    protected function getViewData(): array
    {
        return [
            'apps' => $this->getApps(),
        ];
    }

    protected function getApps(): array
    {
        return Cache::remember('soketi_apps', 60, function () {
            try {
                $response = Http::timeout(5)->get(config('soketi.app_url') . '/soketi/apps');
                return $response->successful() ? $response->json() : [];
            } catch (\Exception $e) {
                logger()->error('Failed to fetch apps: ' . $e->getMessage());
                return [];
            }
        });
    }

    protected function getPollingInterval(): ?string
    {
        return '60s';
    }
}
