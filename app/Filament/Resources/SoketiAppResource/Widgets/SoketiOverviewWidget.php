<?php

namespace App\Filament\Resources\SoketiAppResource\Widgets;

use App\Models\SoketiApp;
use App\Models\SoketiConnection;
use App\Services\SoketiStatsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SoketiOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $statsService = app(SoketiStatsService::class);
        $serverStatus = $statsService->testConnection();

        $totalApps = SoketiApp::count();
        $enabledApps = SoketiApp::where('enabled', true)->count();
        $totalConnections = SoketiConnection::where('is_connected', true)->count();

        // Get connection trend for the last 7 days
        $connectionTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = SoketiConnection::whereDate('connected_at', $date)->count();
            $connectionTrend[] = $count;
        }

        return [
            Stat::make('Server Status', $serverStatus['success'] ? 'Online' : 'Offline')
                ->description($serverStatus['message'])
                ->descriptionIcon($serverStatus['success'] ? 'heroicon-m-signal' : 'heroicon-m-signal-slash')
                ->color($serverStatus['success'] ? 'success' : 'danger'),

            Stat::make('Total Apps', $totalApps)
                ->description("$enabledApps enabled, " . ($totalApps - $enabledApps) . " disabled")
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('primary'),

            Stat::make('Active Connections', $totalConnections)
                ->description('Currently connected clients')
                ->descriptionIcon('heroicon-m-link')
                ->chart($connectionTrend)
                ->color('success'),

            Stat::make('Response Time', $serverStatus['response_time'] ? $serverStatus['response_time'] . 'ms' : 'N/A')
                ->description('Server response time')
                ->descriptionIcon('heroicon-m-clock')
                ->color($serverStatus['response_time'] < 100 ? 'success' : ($serverStatus['response_time'] < 500 ? 'warning' : 'danger')),
        ];
    }
}
