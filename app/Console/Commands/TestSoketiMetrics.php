<?php

namespace App\Console\Commands;

use App\Services\SoketiStatsService;
use Illuminate\Console\Command;

class TestSoketiMetrics extends Command
{
    protected $signature = 'soketi:test-metrics {--app= : Test specific app ID}';
    protected $description = 'Test connection to Soketi metrics endpoint';

    public function handle()
    {
        $this->info('🔍 Testing Soketi Metrics Connection...');
        $this->newLine();

        $statsService = app(SoketiStatsService::class);

        // Test basic connection
        $this->info('📡 Testing metrics endpoint connection...');
        $metricsTest = $statsService->testMetricsConnection();

        if ($metricsTest['success']) {
            $this->info("✅ Connected to metrics endpoint: {$metricsTest['url']}");
            $this->info("📊 Found {$metricsTest['metrics_count']} metrics");
            $this->info("📦 Response size: {$metricsTest['response_size']} bytes");
        } else {
            $this->error("❌ Failed to connect to metrics endpoint: {$metricsTest['url']}");
            $this->error("Error: {$metricsTest['error']}");
            return 1;
        }

        $this->newLine();

        // Show available metrics
        $this->info('📋 Getting available metrics...');
        $allMetrics = $statsService->getAllMetrics();

        $this->info("🔢 Total metrics available: {$allMetrics['total_metrics']}");

        if (!empty($allMetrics['metric_names'])) {
            $this->info('📝 Available metric names:');
            foreach (array_slice($allMetrics['metric_names'], 0, 20) as $metricName) {
                $this->line("  - {$metricName}");
            }

            if (count($allMetrics['metric_names']) > 20) {
                $this->line("  ... and " . (count($allMetrics['metric_names']) - 20) . " more");
            }
        }

        $this->newLine();

        // Test with specific app if provided
        $appId = $this->option('app');
        if ($appId) {
            $this->info("🎯 Testing with specific app: {$appId}");

            $app = \App\Models\SoketiApp::find($appId);
            if (!$app) {
                $this->error("❌ App not found: {$appId}");
                return 1;
            }

            $this->info("📊 Getting app stats...");
            $appStats = $statsService->getAppStats($app);
            $eventStats = $statsService->getEventStats($app);
            $channelStats = $statsService->getChannelStats($app);

            $this->table(['Metric', 'Value'], [
                ['Current Connections', $appStats['current_connections']],
                ['Max Connections', $appStats['max_connections']],
                ['Utilization', $appStats['utilization_percentage'] . '%'],
                ['Server Status', $appStats['status']['healthy'] ? 'Healthy' : 'Offline'],
                ['Events (24h)', $eventStats['events_last_24h']],
                ['Events/Hour', round($eventStats['events_per_hour'], 2)],
                ['Failed Events', $eventStats['failed_events']],
                ['Active Channels', $channelStats['active_channels']],
                ['Total Channels', $channelStats['total_channels']],
            ]);

            if (!empty($eventStats['event_types'])) {
                $this->newLine();
                $this->info('📈 Event Type Breakdown:');
                foreach ($eventStats['event_types'] as $type => $count) {
                    $this->line("  - {$type}: {$count}");
                }
            }
        }

        $this->newLine();
        $this->info('🎉 Metrics test completed!');

        // Recommendations
        $this->newLine();
        $this->info('💡 Recommendations:');

        if ($allMetrics['total_metrics'] === 0) {
            $this->warn('⚠️  No metrics found. Make sure Soketi is running with metrics enabled.');
            $this->line('   Check your soketi-config.json for metrics configuration.');
        } elseif ($allMetrics['total_metrics'] < 10) {
            $this->warn('⚠️  Few metrics found. Soketi might not be fully configured.');
        } else {
            $this->info('✅ Good number of metrics found. Soketi appears to be working correctly.');
        }

        return 0;
    }
}
