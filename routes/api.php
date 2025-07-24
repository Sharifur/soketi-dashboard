<?php

use App\Http\Controllers\Api\SoketiAppApiController;
use App\Http\Controllers\SoketiTestController;
use App\Models\SoketiApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SoketiAppController;
use Pusher\Pusher;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Create a catch-all route that always returns your test app
Route::get('/soketi/apps/{appId}', function ($appId) {
    // For soketi compatibility, always return the test app
    if ($appId === 'T9uf4MLVYU1nX1T7' || $appId === '153722162515') {
        return response()->json([
            'id' => '153722162515',
            'key' => 'T9uf4MLVYU1nX1T7',
            'secret' => '6oOe6mC9KVe4GZsZ',
            'maxConnections' => 1000,
            'enableClientMessages' => true,
            'enabled' => true,
            'maxBackendEventsPerSecond' => 100,
            'maxClientEventsPerSecond' => 50,
            'maxReadRequestsPerSecond' => 100,
            'webhooks' => [],
            'maxPresenceMembersPerChannel' => 100,
            'maxPresenceMemberSizeInKb' => 2,
            'maxChannelNameLength' => 100,
            'maxEventChannelsAtOnce' => 10,
            'maxEventNameLength' => 100,
            'maxEventPayloadInKb' => 100,
            'maxEventBatchSize' => 10,
            'enableUserAuthentication' => false
        ]);
    }

    try {
        // First try to find by ID, then by key (for soketi compatibility)
        $app = SoketiApp::find($appId) ?: SoketiApp::where('key', $appId)->first();

        if (!$app) {
            return response()->json([
                'error' => 'App not found'
            ], 404);
        }

        if (!$app->enabled) {
            return response()->json([
                'error' => 'App is disabled'
            ], 403);
        }

        // Return the exact format soketi expects
        return response()->json([
            'id' => (string) $app->id,
            'key' => (string) $app->key,
            'secret' => (string) $app->secret,
            'maxConnections' => (int) $app->max_connections,
            'enableClientMessages' => (bool) $app->enable_client_messages,
            'enabled' => (bool) $app->enabled,
            'maxBackendEventsPerSecond' => (int) $app->max_backend_events_per_sec,
            'maxClientEventsPerSecond' => (int) $app->max_client_events_per_sec,
            'maxReadRequestsPerSecond' => (int) $app->max_read_req_per_sec,
            'webhooks' => (array) ($app->webhooks ?? []),
            'maxPresenceMembersPerChannel' => (int) $app->max_presence_members_per_channel,
            'maxPresenceMemberSizeInKb' => (int) $app->max_presence_member_size_in_kb,
            'maxChannelNameLength' => (int) $app->max_channel_name_length,
            'maxEventChannelsAtOnce' => (int) $app->max_event_channels_at_once,
            'maxEventNameLength' => (int) $app->max_event_name_length,
            'maxEventPayloadInKb' => (int) $app->max_event_payload_in_kb,
            'maxEventBatchSize' => (int) $app->max_event_batch_size,
            'enableUserAuthentication' => (bool) $app->enable_user_authentication,
        ], 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache'
        ]);

    } catch (\Exception $e) {
        \Log::error('Soketi app lookup failed', [
            'app_id' => $appId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'error' => 'Internal server error'
        ], 500);
    }
});

// Soketi app list endpoint (optional)
Route::get('/soketi/apps', function () {
    try {
        $apps = SoketiApp::where('enabled', true)->get();

        return response()->json(
            $apps->map(function ($app) {
                return [
                    'id' => $app->id,
                    'key' => $app->key,
                    'secret' => $app->secret,
                    'maxConnections' => $app->max_connections,
                    'enableClientMessages' => $app->enable_client_messages,
                    'enabled' => $app->enabled,
                ];
            })
        );

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Internal server error'
        ], 500);
    }
});

// Test endpoint to verify API is working
Route::get('/soketi/test', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'apps_count' => SoketiApp::count(),
        'enabled_apps_count' => SoketiApp::where('enabled', true)->count(),
    ]);
});

// Send test message endpoint
Route::post('/soketi/send-test', function (Request $request) {
    try {
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'host' => env('PUSHER_HOST'),
                'port' => env('PUSHER_PORT'),
                'scheme' => env('PUSHER_SCHEME'),
            ]
        );

        $channel = $request->input('channel', 'test-channel');
        $event = $request->input('event', 'test-event');
        $data = $request->input('data', ['message' => 'Test message from Laravel']);

        $result = $pusher->trigger($channel, $event, $data);

        return response()->json([
            'success' => true,
            'message' => 'Event sent successfully',
            'channel' => $channel,
            'event' => $event,
            'data' => $data,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to send event: ' . $e->getMessage(),
        ], 500);
    }
});

Route::get('soketi/health', function (): JsonResponse
{
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'apps_count' => SoketiApp::count(),
        'enabled_apps_count' => SoketiApp::enabled()->count(),
    ]);
});

Route::get('soketi/metrics', function (): JsonResponse
{
    try {
        $metrics = Cache::remember('soketi_metrics_summary', 60, function () {
            // You can fetch actual metrics from Prometheus here
            // For now, we'll return basic stats
            return [
                'total_apps' => SoketiApp::count(),
                'enabled_apps' => SoketiApp::enabled()->count(),
                'apps_with_webhooks' => SoketiApp::whereNotNull('webhooks')->count(),
                'apps_with_client_messages' => SoketiApp::withClientMessages()->count(),
                'total_max_connections' => SoketiApp::enabled()->sum('max_connections'),
            ];
        });

        return response()->json($metrics);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to fetch metrics',
            'message' => $e->getMessage()
        ], 500);
    }
});
