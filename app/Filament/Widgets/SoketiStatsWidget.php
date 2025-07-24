<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SoketiStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $metrics = $this->getSoketiMetrics();
        $serverStatus = $this->getServerStatus();
        $apps = $this->getApps();

        return [
            Stat::make('Server Status', $serverStatus['online'] ? 'Online' : 'Offline')
                ->description($serverStatus['online'] ? 'Running smoothly' : 'Server is down')
                ->descriptionIcon($serverStatus['online'] ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($serverStatus['online'] ? 'success' : 'danger'),

            Stat::make('Total Connections', $metrics['soketi_connections_total'] ?? 0)
                ->description('Active WebSocket connections')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Total Messages', number_format($metrics['soketi_messages_total'] ?? 0))
                ->description('Messages sent today')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('primary'),

            Stat::make('Active Apps', count($apps))
                ->description('Configured applications')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('warning'),
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

        try {
            $response = Http::timeout(3)->get('http://127.0.0.1:6001');
            $isOnline = $response->status() === 200;
        } catch (\Exception $e) {
            // Server is offline
        }

        return [
            'online' => $isOnline,
            'last_check' => now(),
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }
}
