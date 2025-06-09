<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SoketiStatsService
{
    protected string $metricsUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->metricsUrl = config('soketi.metrics_url', 'http://localhost:9601/metrics');
        $this->timeout = config('soketi.timeout', 5);
    }

    /**
     * Get server statistics from Soketi metrics endpoint
     */
    public function getServerStats(): array
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->metricsUrl);

            if ($response->successful()) {
                $metrics = $this->parsePrometheusMetrics($response->body());

                return [
                    'status' => 'Online',
                    'last_check' => now()->format('H:i:s'),
                    'uptime' => $this->formatUptime($metrics['soketi_uptime'] ?? 0),
                    'availability' => '99.9%', // Calculate from historical data
                    'connections_total' => $metrics['soketi_connections_total'] ?? 0,
                    'messages_total' => $metrics['soketi_messages_total'] ?? 0,
                    'memory_usage' => $metrics['process_resident_memory_bytes'] ?? 0,
                    'cpu_usage' => $metrics['process_cpu_seconds_total'] ?? 0,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Soketi metrics', [
                'error' => $e->getMessage(),
                'url' => $this->metricsUrl,
            ]);
        }

        return [
            'status' => 'Offline',
            'last_check' => now()->format('H:i:s'),
            'uptime' => 'Unknown',
            'availability' => '0%',
            'connections_total' => 0,
            'messages_total' => 0,
            'memory_usage' => 0,
            'cpu_usage' => 0,
        ];
    }

    /**
     * Get real-time connection count for a specific app
     */
    public function getAppConnections(string $appId): int
    {
        $cacheKey = "soketi_app_connections_{$appId}";

        return Cache::remember($cacheKey, 10, function () use ($appId) {
            try {
                $response = Http::timeout($this->timeout)->get($this->metricsUrl);

                if ($response->successful()) {
                    $metrics = $this->parsePrometheusMetrics($response->body());

                    // Look for app-specific connection metrics
                    return $metrics["soketi_connections_total{app_id=\"{$appId}\"}"] ?? 0;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch app connections', [
                    'app_id' => $appId,
                    'error' => $e->getMessage(),
                ]);
            }

            return 0;
        });
    }

    /**
     * Get connection history for charts
     */
    public function getConnectionHistory(int $hours = 24): array
    {
        $cacheKey = "soketi_connection_history_{$hours}h";

        return Cache::remember($cacheKey, 300, function () use ($hours) {
            // This would typically query your metrics storage (InfluxDB, Prometheus, etc.)
            // For now, we'll generate sample data

            $data = [];
            $baseConnections = rand(10, 100);

            for ($i = $hours; $i >= 0; $i--) {
                $timestamp = now()->subHours($i);
                $connections = $baseConnections + rand(-20, 30);
                $connections = max(0, $connections); // Ensure non-negative

                $data[] = [
                    'timestamp' => $timestamp->toISOString(),
                    'connections' => $connections,
                    'hour' => $timestamp->format('H:i'),
                ];

                $baseConnections = $connections; // Create realistic fluctuation
            }

            return $data;
        });
    }

    /**
     * Get message throughput statistics
     */
    public function getMessageThroughput(): array
    {
        return Cache::remember('soketi_message_throughput', 60, function () {
            try {
                $response = Http::timeout($this->timeout)->get($this->metricsUrl);

                if ($response->successful()) {
                    $metrics = $this->parsePrometheusMetrics($response->body());

                    return [
                        'messages_per_second' => $metrics['soketi_messages_rate'] ?? 0,
                        'total_messages' => $metrics['soketi_messages_total'] ?? 0,
                        'failed_messages' => $metrics['soketi_messages_failed_total'] ?? 0,
                        'success_rate' => $this->calculateSuccessRate($metrics),
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch message throughput', [
                    'error' => $e->getMessage(),
                ]);
            }

            return [
                'messages_per_second' => 0,
                'total_messages' => 0,
                'failed_messages' => 0,
                'success_rate' => 100,
            ];
        });
    }

    /**
     * Test connection to Soketi server
     */
    public function testConnection(): array
    {
        $startTime = microtime(true);

        try {
            $response = Http::timeout($this->timeout)->get($this->metricsUrl);
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status_code' => $response->status(),
                    'response_time' => $responseTime,
                    'message' => 'Connection successful',
                ];
            }

            return [
                'success' => false,
                'status_code' => $response->status(),
                'response_time' => $responseTime,
                'message' => 'Server returned error: ' . $response->status(),
            ];
        } catch (\Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'success' => false,
                'status_code' => 0,
                'response_time' => $responseTime,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Parse Prometheus metrics format
     */
    protected function parsePrometheusMetrics(string $content): array
    {
        $metrics = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            // Parse metric line
            if (preg_match('/^([a-zA-Z_:][a-zA-Z0-9_:]*(?:\{[^}]*\})?) ([0-9.+-eE]+)/', $line, $matches)) {
                $metricName = $matches[1];
                $metricValue = (float) $matches[2];
                $metrics[$metricName] = $metricValue;
            }
        }

        return $metrics;
    }

    /**
     * Format uptime in human-readable format
     */
    protected function formatUptime(float $seconds): string
    {
        if ($seconds < 60) {
            return round($seconds) . 's';
        }

        if ($seconds < 3600) {
            return round($seconds / 60) . 'm';
        }

        if ($seconds < 86400) {
            $hours = floor($seconds / 3600);
            $minutes = round(($seconds % 3600) / 60);
            return "{$hours}h {$minutes}m";
        }

        $days = floor($seconds / 86400);
        $hours = round(($seconds % 86400) / 3600);
        return "{$days}d {$hours}h";
    }

    /**
     * Calculate message success rate
     */
    protected function calculateSuccessRate(array $metrics): float
    {
        $total = $metrics['soketi_messages_total'] ?? 0;
        $failed = $metrics['soketi_messages_failed_total'] ?? 0;

        if ($total == 0) {
            return 100;
        }

        return round((($total - $failed) / $total) * 100, 2);
    }
}
