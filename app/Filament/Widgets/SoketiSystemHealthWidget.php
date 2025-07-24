<?php

namespace App\Filament\Widgets;

use App\Models\SoketiApp;
use App\Models\SoketiConnection;
use App\Services\SoketiStatsService;
use Filament\Widgets\Widget;

class SoketiSystemHealthWidget extends Widget
{
    protected static string $view = 'filament.widgets.soketi-system-health';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $statsService = app(SoketiStatsService::class);
        $serverStatus = $statsService->testConnection();
        $systemStats = $statsService->getSystemStats();
        $metricsData = $statsService->getMetrics();

        // Get apps with high utilization
        $highUtilizationApps = SoketiApp::where('enabled', true)
            ->get()
            ->filter(function ($app) {
                return $app->getConnectionUtilization() > 80;
            });

        // Get recent disconnections (possible issues)
        $recentDisconnections = SoketiConnection::where('disconnected_at', '>=', now()->subMinutes(5))
            ->where('is_connected', false)
            ->count();

        // Format metrics for the view
        $soketi_metrics = [
            'available' => $metricsData['success'] ?? false,
            'connections' => $this->parseMetricValue($metricsData, 'connections') ?? 0,
            'messages' => $this->parseMetricValue($metricsData, 'messages') ?? 0,
        ];

        return [
            'server_status' => $serverStatus,
            'system_stats' => $systemStats,
            'highUtilization_apps' => $highUtilizationApps,
            'recent_disconnections' => $recentDisconnections,
            'health_score' => $this->calculateHealthScore($serverStatus, $systemStats, $highUtilizationApps, $recentDisconnections),
            'soketi_status' => $serverStatus,
            'soketi_metrics' => $soketi_metrics,
        ];
    }

    private function parseMetricValue($metricsData, $metric): ?int
    {
        if (!isset($metricsData['data'])) {
            return null;
        }

        // Parse metrics from the response data
        // This depends on your metrics format - adjust as needed
        $data = $metricsData['data'];

        if (is_array($data)) {
            return $data[$metric] ?? null;
        }

        // If it's a string (like Prometheus format), parse it
        if (is_string($data)) {
            if (preg_match("/soketi_{$metric}_total\\s+(\\d+)/", $data, $matches)) {
                return (int) $matches[1];
            }
        }

        return null;
    }

    protected function calculateHealthScore($serverStatus, $systemStats, $highUtilizationApps, $recentDisconnections): array
    {
        $score = 100;
        $issues = [];

        if (!$serverStatus['success']) {
            $score -= 50;
            $issues[] = 'Soketi server is offline';
        }

        if ($systemStats['active_apps'] === 0 && $systemStats['total_apps'] > 0) {
            $score -= 30;
            $issues[] = 'No active applications';
        }

        if ($highUtilizationApps->count() > 0) {
            $score -= 20;
            $issues[] = $highUtilizationApps->count() . ' apps near capacity';
        }

        if ($recentDisconnections > 10) {
            $score -= 15;
            $issues[] = 'High disconnection rate detected';
        }

        return [
            'score' => max(0, $score),
            'status' => $score >= 90 ? 'excellent' : ($score >= 70 ? 'good' : ($score >= 50 ? 'warning' : 'critical')),
            'issues' => $issues,
        ];
    }
}
