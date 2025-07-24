<?php


use App\Models\SoketiApp;
use App\Models\SoketiConnection;
use App\Services\SoketiStatsService;
use Filament\Widgets\Widget;

class SoketiSystemHealth extends Widget
{
    protected static string $view = 'filament.widgets.soketi-system-health';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $statsService = app(SoketiStatsService::class);
        $serverStatus = $statsService->testConnection();
        $systemStats = $statsService->getSystemStats();

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

        return [
            'serverStatus' => $serverStatus,
            'systemStats' => $systemStats,
            'highUtilizationApps' => $highUtilizationApps,
            'recentDisconnections' => $recentDisconnections,
            'healthScore' => $this->calculateHealthScore($serverStatus, $systemStats, $highUtilizationApps, $recentDisconnections),
        ];
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
