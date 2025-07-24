]
<?php

use App\Http\Controllers\SoketiTestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SoketiWebhookController;


Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/test-soketi-metrics', function () {
    try {
        $statsService = app(\App\Services\SoketiStatsService::class);

        // Test basic connection
        $metricsTest = $statsService->testMetricsConnection();
        $allMetrics = $statsService->getAllMetrics();

        // Find app IDs in metrics
        $appIds = collect($allMetrics['sample_metrics'] ?? [])
            ->pluck('labels')
            ->filter(fn($labels) => isset($labels['app_id']))
            ->pluck('app_id')
            ->unique()
            ->values();

        // Check database apps
        $dbApps = \App\Models\SoketiApp::all(['id', 'key'])->map(function($app) {
            return ['id' => $app->id, 'key' => $app->key];
        });

        return response()->json([
            'connection_test' => $metricsTest,
            'available_metrics' => $allMetrics,
            'app_ids_in_metrics' => $appIds,
            'apps_in_database' => $dbApps,
            'config' => [
                'metrics_url' => config('soketi.metrics_url', 'http://127.0.0.1:9601/metrics'),
                'server_url' => config('soketi.server_url', 'http://127.0.0.1:6001'),
            ]
        ], 200, [], JSON_PRETTY_PRINT);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500, [], JSON_PRETTY_PRINT);
    }
});

// Test with the actual app ID from metrics
Route::get('/test-soketi-real-app', function () {
    try {
        // Use the app ID we see in your metrics: 197234158731
        $realAppId = '197234158731';

        // First check if this app exists in database
        $app = \App\Models\SoketiApp::find($realAppId);
        if (!$app) {
            // Create a temporary app record for testing
            $app = new \App\Models\SoketiApp();
            $app->id = $realAppId;
            $app->key = 'temp-key';
            $app->secret = 'temp-secret';
            $app->max_connections = 1000;
            // Don't save to database, just use for testing
        }

        $statsService = app(\App\Services\SoketiStatsService::class);

        $appStats = $statsService->getAppStats($app);
        $eventStats = $statsService->getEventStats($app);
        $channelStats = $statsService->getChannelStats($app);

        return response()->json([
            'app_id' => $realAppId,
            'app_exists_in_db' => \App\Models\SoketiApp::find($realAppId) !== null,
            'app_stats' => $appStats,
            'event_stats' => $eventStats,
            'channel_stats' => $channelStats,
        ], 200, [], JSON_PRETTY_PRINT);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'app_id' => '197234158731',
        ], 500, [], JSON_PRETTY_PRINT);
    }
});

// Debug chart data specifically
Route::get('/debug-chart-data/{appId}', function ($appId) {
    try {
        $app = \App\Models\SoketiApp::findOrFail($appId);
        $statsService = app(\App\Services\SoketiStatsService::class);

        $appStats = $statsService->getAppStats($app);
        $hourlyData = $appStats['hourly_data'] ?? [];

        return response()->json([
            'app_id' => $appId,
            'current_connections' => $appStats['current_connections'],
            'hourly_data' => $hourlyData,
            'chart_debug' => [
                'data_count' => count($hourlyData['data'] ?? []),
                'labels_count' => count($hourlyData['labels'] ?? []),
                'max_value' => max($hourlyData['data'] ?? [0]),
                'min_value' => min($hourlyData['data'] ?? [0]),
                'avg_value' => count($hourlyData['data'] ?? []) > 0 ? array_sum($hourlyData['data']) / count($hourlyData['data']) : 0,
            ],
            'raw_data' => $hourlyData['data'] ?? [],
            'raw_labels' => $hourlyData['labels'] ?? [],
        ], 200, [], JSON_PRETTY_PRINT);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'app_id' => $appId,
        ], 500, [], JSON_PRETTY_PRINT);
    }
});

// Force chart data generation
Route::get('/force-chart-refresh/{appId}', function ($appId) {
    try {
        // Clear cache first
        \Illuminate\Support\Facades\Cache::forget("soketi_app_stats_{$appId}");

        $app = \App\Models\SoketiApp::findOrFail($appId);
        $statsService = app(\App\Services\SoketiStatsService::class);

        $appStats = $statsService->getAppStats($app);

        return response()->json([
            'message' => 'Chart data refreshed',
            'app_id' => $appId,
            'current_connections' => $appStats['current_connections'],
            'hourly_data' => $appStats['hourly_data'],
        ], 200, [], JSON_PRETTY_PRINT);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'app_id' => $appId,
        ], 500, [], JSON_PRETTY_PRINT);
    }
});

// Test with a specific app
Route::get('/test-soketi-app/{appId}', function ($appId) {
    try {
        $app = \App\Models\SoketiApp::findOrFail($appId);
        $statsService = app(\App\Services\SoketiStatsService::class);

        $appStats = $statsService->getAppStats($app);
        $eventStats = $statsService->getEventStats($app);
        $channelStats = $statsService->getChannelStats($app);

        return response()->json([
            'app_id' => $appId,
            'app_stats' => $appStats,
            'event_stats' => $eventStats,
            'channel_stats' => $channelStats,
        ], 200, [], JSON_PRETTY_PRINT);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'app_id' => $appId,
        ], 500, [], JSON_PRETTY_PRINT);
    }
});

// Quick manual metrics fetch
Route::get('/test-manual-metrics', function () {
    try {
        $metricsUrl = config('soketi.metrics_url', 'http://127.0.0.1:9601/metrics');

        $response = \Illuminate\Support\Facades\Http::timeout(10)->get($metricsUrl);

        if ($response->successful()) {
            $body = $response->body();
            $lines = explode("\n", $body);

            // Extract app IDs from metrics
            $appIds = [];
            foreach ($lines as $line) {
                if (preg_match('/app_id="([^"]+)"/', $line, $matches)) {
                    $appIds[] = $matches[1];
                }
            }
            $appIds = array_unique($appIds);

            // Show first 20 lines of metrics
            $sampleLines = array_slice($lines, 0, 20);

            return response()->json([
                'success' => true,
                'url' => $metricsUrl,
                'status_code' => $response->status(),
                'total_lines' => count($lines),
                'response_size' => strlen($body),
                'app_ids_found' => $appIds,
                'sample_lines' => $sampleLines,
            ], 200, [], JSON_PRETTY_PRINT);
        } else {
            return response()->json([
                'success' => false,
                'url' => $metricsUrl,
                'status_code' => $response->status(),
                'error' => $response->body(),
            ], 500, [], JSON_PRETTY_PRINT);
        }

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'url' => config('soketi.metrics_url', 'http://127.0.0.1:9601/metrics'),
        ], 500, [], JSON_PRETTY_PRINT);
    }
});

Route::post('soketi/webhook', [SoketiWebhookController::class, 'handle']);


// Soketi test routes
Route::middleware(['auth'])->group(function () {
    // Main test page
    Route::get('/soketi/test/{soketiApp}', [SoketiTestController::class, 'test'])->name('soketi.test');

    // API endpoints for testing
    Route::get('/api/soketi/test/{soketiApp}', [SoketiTestController::class, 'testApi'])->name('soketi.test.api');
    Route::post('/api/soketi/send-test/{soketiApp}', [SoketiTestController::class, 'sendTestMessage'])->name('soketi.send.test');
    Route::get('/api/soketi/channel-info/{soketiApp}', [SoketiTestController::class, 'getChannelInfo'])->name('soketi.channel.info');

    // Webhook test routes (if you have webhook functionality)
    Route::get('/webhook/test/{webhook}', [\App\Http\Controllers\WebhookTestController::class, 'test'])->name('webhook.test');
    Route::post('/api/webhook/test/{webhook}', [\App\Http\Controllers\WebhookTestController::class, 'sendTest'])->name('webhook.test.send');
});
