<?php

namespace App\Filament\Widgets;

use App\Models\SoketiApp;
use App\Models\SoketiConnection;
use App\Services\SoketiStatsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class SoketiStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Cache stats for 30 seconds to reduce load
        $stats = Cache::remember('soketi_dashboard_stats', 30, function () {
            return $this->calculateStats();
        });

        return [
            Stat::make('Total Applications', $stats['total_apps'])
                ->description($stats['active_apps'] . ' active applications')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('primary')
                ->chart($stats['apps_chart']),

            Stat::make('Active Connections', number_format($stats['active_connections']))
                ->description($this->getConnectionsDescription($stats))
                ->descriptionIcon('heroicon-m-signal')
                ->color($this->getConnectionsColor($stats))
                ->chart($stats['connections_chart']),

            Stat::make('Server Status', $stats['server_status'])
                ->description('Last checked: ' . $stats['last_check'])
                ->descriptionIcon($stats['server_status'] === 'Online' ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($stats['server_status'] === 'Online' ? 'success' : 'danger'),

            Stat::make('Messages Today', number_format($stats['messages_today']))
                ->description($stats['messages_change'] . ' from yesterday')
                ->descriptionIcon($stats['messages_trend_icon'])
                ->color($stats['messages_trend_color'])
                ->chart($stats['messages_chart']),

            Stat::make('Peak Connections', number_format($stats['peak_connections']))
                ->description('Today\'s peak at ' . $stats['peak_time'])
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),

            Stat::make('Uptime', $stats['uptime'])
                ->description('System availability: ' . $stats['availability'])
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
        ];
    }

    protected function calculateStats(): array
    {
        $totalApps = SoketiApp::count();
        $activeApps = SoketiApp::where('is_active', true)->count();
        $activeConnections = SoketiConnection::where('is_connected', true)->count();

        // Get server stats from Soketi metrics
        $soketiStats = app(SoketiStatsService::class)->getServerStats();

        // Calculate 7-day trend for apps
        $appsChart = $this->getAppsChart();

        // Calculate 24-hour trend for connections
        $connectionsChart = $this->getConnectionsChart();

        // Calculate messages stats
        $messagesStats = $this->getMessagesStats();

        // Calculate peak connections for today
        $peakStats = $this->getPeakConnectionsStats();

        return [
            'total_apps' => $totalApps,
            'active_apps' => $activeApps,
            'active_connections' => $activeConnections,
            'server_status' => $soketiStats['status'] ?? 'Unknown',
            'last_check' => $soketiStats['last_check'] ?? 'Never',
            'messages_today' => $messagesStats['today'],
            'messages_change' => $messagesStats['change'],
            'messages_trend_icon' => $messagesStats['trend_icon'],
            'messages_trend_color' => $messagesStats['trend_color'],
            'messages_chart' => $messagesStats['chart'],
            'peak_connections' => $peakStats['peak'],
            'peak_time' => $peakStats['time'],
            'uptime' => $soketiStats['uptime'] ?? 'Unknown',
            'availability' => $soketiStats['availability'] ?? '0%',
            'apps_chart' => $appsChart,
            'connections_chart' => $connectionsChart,
        ];
    }

    protected function getConnectionsDescription(array $stats): string
    {
        $totalCapacity = SoketiApp::where('is_active', true)
            ->where('max_connections', '>', 0)
            ->sum('max_connections');

        if ($totalCapacity > 0) {
            $usage = round(($stats['active_connections'] / $totalCapacity) * 100, 1);
            return "Usage: {$usage}% of capacity";
        }

        return 'Unlimited capacity';
    }

    protected function getConnectionsColor(array $stats): string
    {
        $totalCapacity = SoketiApp::where('is_active', true)
            ->where('max_connections', '>', 0)
            ->sum('max_connections');

        if ($totalCapacity > 0) {
            $usage = ($stats['active_connections'] / $totalCapacity) * 100;

            if ($usage >= 90) return 'danger';
            if ($usage >= 70) return 'warning';
            if ($usage >= 50) return 'info';
        }

        return 'success';
    }

    protected function getAppsChart(): array
    {
        // Get app creation count for last 7 days
        $days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->startOfDay();
            return SoketiApp::whereDate('created_at', $date)->count();
        });

        return $days->toArray();
    }

    protected function getConnectionsChart(): array
    {
        // Get hourly connection counts for last 24 hours
        $hours = collect(range(23, 0))->map(function ($hoursAgo) {
            $hour = now()->subHours($hoursAgo)->startOfHour();
            return SoketiConnection::where('connected_at', '>=', $hour)
                ->where('connected_at', '<', $hour->copy()->addHour())
                ->count();
        });

        return $hours->toArray();
    }

    protected function getMessagesStats(): array
    {
        // This would typically come from your metrics system
        // For now, we'll simulate some data
        $today = rand(1000, 10000);
        $yesterday = rand(800, 9000);

        $change = $today - $yesterday;
        $changePercent = $yesterday > 0 ? round(($change / $yesterday) * 100, 1) : 0;

        if ($change > 0) {
            $changeText = "+{$changePercent}%";
            $trendIcon = 'heroicon-m-arrow-trending-up';
            $trendColor = 'success';
        } elseif ($change < 0) {
            $changeText = "{$changePercent}%";
            $trendIcon = 'heroicon-m-arrow-trending-down';
            $trendColor = 'danger';
        } else {
            $changeText = 'No change';
            $trendIcon = 'heroicon-m-minus';
            $trendColor = 'gray';
        }

        // Generate chart data for last 7 days
        $chart = collect(range(6, 0))->map(function () {
            return rand(500, 2000);
        })->toArray();

        return [
            'today' => $today,
            'change' => $changeText,
            'trend_icon' => $trendIcon,
            'trend_color' => $trendColor,
            'chart' => $chart,
        ];
    }

    public function getPeakConnectionsStats(): ?array
    {
        $today = now()->startOfDay();

        $driver = \DB::getDriverName();

        $timeFormat = $driver === 'sqlite'
            ? "strftime('%H:%M', connected_at)"
            : "DATE_FORMAT(connected_at, '%H:%i')";

        $peak = SoketiConnection::where('connected_at', '>=', $today)
            ->selectRaw("COUNT(*) as count, {$timeFormat} as time")
            ->groupBy('time')
            ->orderByDesc('count')
            ->first();

        if (!$peak) {
            return [
                'time' => 'N/A',
                'count' => 0,
            ];
        }

        return [
            'time' => $peak->time,
            'count' => $peak->count,
        ];
    }
    protected function getPollingInterval(): ?string
    {
        return '30s'; // Refresh every 30 seconds
    }
}
