<?php

namespace App\Http\Controllers;

use App\Models\SoketiApp;
use App\Services\SoketiStatsService;
use Illuminate\Http\Request;

class SoketiTestController extends Controller
{
    public function test(SoketiApp $soketiApp, SoketiStatsService $statsService)
    {
        $testResults = [
            'app' => $soketiApp,
            'server_test' => $statsService->testConnection(),
            'timestamp' => now(),
        ];

        return view('soketi.test', compact('testResults'));
    }

    public function testApi(SoketiApp $soketiApp, SoketiStatsService $statsService)
    {
        $serverTest = $statsService->testConnection();

        return response()->json([
            'app_id' => $soketiApp->app_id,
            'app_name' => $soketiApp->name,
            'server_status' => $serverTest['success'] ? 'online' : 'offline',
            'response_time' => $serverTest['response_time'] ?? 0,
            'message' => $serverTest['message'] ?? 'Unknown status',
            'timestamp' => now()->toISOString(),
        ]);
    }
}
