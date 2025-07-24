<?php

namespace App\Filament\Widgets;

use App\Models\SoketiApp;
use App\Models\SoketiConnection;
use App\Models\SoketiDebugEvent;
use App\Services\SoketiStatsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SoketiDashboardOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $statsService = app(SoketiStatsService::class);
        $serverStatus = $statsService->testConnection();

        // Get connection trend data for the last 7 days
        $connectionTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = SoketiConnection::whereDate('connected_at', $date)->count();
            $connectionTrend[] = $count;
        }

        // Get event trend data for the last 7 days
        $eventTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = SoketiDebugEvent::whereDate('timestamp', $date)->count();
            $eventTrend[] = $count;
        }

        $totalApps = SoketiApp::count();
        $activeApps = SoketiApp::where('enabled', true)->count();
        $activeConnections = SoketiConnection::where('is_connected', true)->count();
        $eventsToday = SoketiDebugEvent::whereDate('timestamp', today())->count();

        // Calculate capacity utilization across all apps
        $totalCapacity = SoketiApp::where('enabled', true)->sum('max_connections');
        $utilizationPercentage = $totalCapacity > 0 ? round(($activeConnections / $totalCapacity) * 100, 1) : 0;

        return [
            Stat::make('Server Status', $serverStatus['success'] ? 'Online' : 'Offline')
                ->description($serverStatus['success']
                    ? "Response time: {$serverStatus['response_time']}ms"
                    : $serverStatus['message'])
                ->descriptionIcon($serverStatus['success'] ? 'heroicon-m-signal' : 'heroicon-m-signal-slash')
                ->color($serverStatus['success'] ? 'success' : 'danger'),

            Stat::make('Applications', $activeApps . ' / ' . $totalApps)
                ->description($activeApps . ' enabled, ' . ($totalApps - $activeApps) . ' disabled')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->chart($connectionTrend)
                ->color('primary'),

            Stat::make('Active Connections', $activeConnections)
                ->description("Capacity: {$utilizationPercentage}% used")
                ->descriptionIcon('heroicon-m-link')
                ->chart($connectionTrend)
                ->color($utilizationPercentage > 80 ? 'danger' : ($utilizationPercentage > 60 ? 'warning' : 'success')),

            Stat::make('Events Today', number_format($eventsToday))
                ->description('WebSocket events processed')
                ->descriptionIcon('heroicon-m-bolt')
                ->chart($eventTrend)
                ->color('info'),
        ];
    }

    protected static bool $isLazy = false;
}
