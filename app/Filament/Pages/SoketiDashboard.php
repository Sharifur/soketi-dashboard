<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SoketiStatsWidget;
use App\Filament\Widgets\SoketiConnectionsChart;
use App\Filament\Widgets\SoketiAppsWidget;
use App\Filament\Widgets\SoketiEventsChart;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SoketiDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-signal';
    protected static string $view = 'filament.pages.soketi-dashboard';
    protected static ?string $title = 'Soketi Analytics';
    protected static ?string $navigationLabel = 'Socket Analytics';
    protected static ?int $navigationSort = 1;

    public $refreshInterval = 30; // seconds

    protected function getHeaderWidgets(): array
    {
        return [
            SoketiStatsWidget::class,
            SoketiConnectionsChart::class,
            SoketiAppsWidget::class,
            SoketiEventsChart::class,
        ];
    }

    public function getViewData(): array
    {
        return [
            'metrics' => $this->getSoketiMetrics(),
            'apps' => $this->getApps(),
            'serverStatus' => $this->getServerStatus(),
        ];
    }

    protected function getSoketiMetrics(): array
    {
        return Cache::remember('soketi_metrics', 30, function () {
            try {
                $response = Http::timeout(5)->get('http://127.0.0.1:9601/metrics');

                if ($response->successful()) {
                    return $this->parsePrometheusMetrics($response->body());
                }
            } catch (\Exception $e) {
                logger()->error('Failed to fetch Soketi metrics: ' . $e->getMessage());
            }

            return [];
        });
    }

    protected function parsePrometheusMetrics(string $metricsText): array
    {
        $metrics = [];
        $lines = explode("\n", $metricsText);

        foreach ($lines as $line) {
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            if (preg_match('/^([a-zA-Z_:][a-zA-Z0-9_:]*)\s+([0-9.+-eE]+)$/', $line, $matches)) {
                $metricName = $matches[1];
                $value = (float) $matches[2];
                $metrics[$metricName] = $value;
            }
        }

        return $metrics;
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

    protected function getServerStatus(): array
    {
        $isOnline = false;
        $uptime = 0;

        try {
            $response = Http::timeout(3)->get('http://127.0.0.1:6001');
            $isOnline = $response->status() === 200;
        } catch (\Exception $e) {
            // Server is offline
        }

        return [
            'online' => $isOnline,
            'uptime' => $uptime,
            'last_check' => now(),
        ];
    }

    // Method to refresh the page data (can be called from Livewire)
    public function refreshData(): void
    {
        // Clear the relevant caches
        Cache::forget('soketi_metrics');
        Cache::forget('soketi_apps');

        // Trigger a page refresh
        $this->redirect(request()->header('Referer'));
    }

    // Get polling interval for auto-refresh
    public function getPollingInterval(): ?string
    {
        return '30s';
    }
}
