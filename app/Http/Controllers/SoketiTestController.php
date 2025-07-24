<?php

namespace App\Http\Controllers;

use App\Models\SoketiApp;
use App\Services\SoketiStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;
use Exception;

class SoketiTestController extends Controller
{
    private SoketiStatsService $statsService;

    public function __construct(SoketiStatsService $statsService)
    {
        $this->statsService = $statsService;
    }

    public function test(SoketiApp $soketiApp)
    {
        $testResults = [
            'app' => $soketiApp,
            'server_test' => $this->statsService->testConnection(),
            'timestamp' => now(),
            'config' => $this->getPusherConfig($soketiApp),
        ];

        return view('soketi.test', compact('testResults'));
    }

    public function testApi(SoketiApp $soketiApp)
    {
        $serverTest = $this->statsService->testConnection();

        return response()->json([
            'app_id' => $soketiApp->id,
            'app_name' => $soketiApp->app_name,
            'server_status' => $serverTest['success'] ? 'online' : 'offline',
            'response_time' => $serverTest['response_time'] ?? 0,
            'message' => $serverTest['message'] ?? 'Unknown status',
            'timestamp' => now()->toISOString(),
            'config' => $this->getPusherConfig($soketiApp),
        ]);
    }

    public function sendTestMessage(Request $request, SoketiApp $soketiApp)
    {
        try {
            $pusher = new Pusher(
                $soketiApp->key,
                $soketiApp->secret,
                $soketiApp->id,
                [
                    'host' => config('broadcasting.connections.pusher.options.host', '127.0.0.1'),
                    'port' => config('broadcasting.connections.pusher.options.port', 6001),
                    'scheme' => config('broadcasting.connections.pusher.options.scheme', 'http'),
                    'encrypted' => config('broadcasting.connections.pusher.options.encrypted', false),
                    'useTLS' => config('broadcasting.connections.pusher.options.encrypted', false),
                ]
            );

            $channel = $request->input('channel', 'test-channel');
            $event = $request->input('event', 'server-message');
            $data = $request->input('data', [
                'message' => 'Hello from server!',
                'timestamp' => now()->toISOString(),
                'source' => 'test-controller'
            ]);

            $result = $pusher->trigger($channel, $event, $data);

            Log::info('Soketi test message sent', [
                'app_id' => $soketiApp->id,
                'channel' => $channel,
                'event' => $event,
                'result' => $result
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test message sent successfully',
                'channel' => $channel,
                'event' => $event,
                'data' => $data
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send Soketi test message', [
                'app_id' => $soketiApp->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test message: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getChannelInfo(Request $request, SoketiApp $soketiApp)
    {
        try {
            $pusher = new Pusher(
                $soketiApp->key,
                $soketiApp->secret,
                $soketiApp->id,
                [
                    'host' => config('broadcasting.connections.pusher.options.host', '127.0.0.1'),
                    'port' => config('broadcasting.connections.pusher.options.port', 6001),
                    'scheme' => config('broadcasting.connections.pusher.options.scheme', 'http'),
                    'encrypted' => config('broadcasting.connections.pusher.options.encrypted', false),
                    'useTLS' => config('broadcasting.connections.pusher.options.encrypted', false),
                ]
            );

            $channel = $request->input('channel', 'test-channel');
            $info = $pusher->getChannelInfo($channel);

            return response()->json([
                'success' => true,
                'channel' => $channel,
                'info' => $info
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get channel info: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getPusherConfig(SoketiApp $soketiApp): array
    {
        return [
            'app_id' => $soketiApp->id,
            'key' => $soketiApp->key,
            'secret' => substr($soketiApp->secret, 0, 8) . '...', // Partially hide secret
            'host' => config('broadcasting.connections.pusher.options.host', '127.0.0.1'),
            'port' => config('broadcasting.connections.pusher.options.port', 6001),
            'scheme' => config('broadcasting.connections.pusher.options.scheme', 'http'),
            'encrypted' => config('broadcasting.connections.pusher.options.encrypted', false),
            'cluster' => config('broadcasting.connections.pusher.options.cluster', 'mt1'),
        ];
    }
}
