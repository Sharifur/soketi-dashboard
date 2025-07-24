@php
    $statsService = app(\App\Services\SoketiStatsService::class);
    $appStats = $statsService->getAppStats($app);
    $eventStats = $statsService->getEventStats($app);
    $channelStats = $statsService->getChannelStats($app);

    // Debug mode - remove this in production
    $debugMode = config('app.debug', false);
    if ($debugMode) {
        $metricsTest = $statsService->testMetricsConnection();
        $allMetrics = $statsService->getAllMetrics();
    }
@endphp

@if($debugMode && isset($metricsTest))
    <!-- Debug Information (remove in production) -->
    <div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg">
        <details class="text-sm">
            <summary class="font-medium cursor-pointer">Debug: Metrics Connection Test</summary>
            <div class="mt-2 space-y-2">
                <p><strong>Status:</strong> {{ $metricsTest['success'] ? 'Connected' : 'Failed' }}</p>
                <p><strong>URL:</strong> {{ $metricsTest['url'] }}</p>
                <p><strong>Response Size:</strong> {{ $metricsTest['response_size'] ?? 0 }} bytes</p>
                @if(isset($metricsTest['metrics_count']))
                    <p><strong>Metrics Found:</strong> {{ $metricsTest['metrics_count'] }}</p>
                @endif
                @if(isset($metricsTest['error']))
                    <p class="text-red-600"><strong>Error:</strong> {{ $metricsTest['error'] }}</p>
                @endif
                @if(isset($allMetrics['metric_names']))
                    <details>
                        <summary>Available Metric Names ({{ count($allMetrics['metric_names']) }})</summary>
                        <div class="mt-1 text-xs font-mono bg-gray-100 dark:bg-gray-800 p-2 rounded max-h-32 overflow-y-auto">
                            @foreach($allMetrics['metric_names'] as $metricName)
                                <div>{{ $metricName }}</div>
                            @endforeach
                        </div>
                    </details>
                @endif
            </div>
        </details>
    </div>
@endif

<!-- Main Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Connection Status -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Connection Status</p>
                <div class="flex items-center space-x-2 mt-1">
                    <div class="w-2 h-2 rounded-full {{ $appStats['status']['healthy'] ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    <span class="text-lg font-semibold {{ $appStats['status']['healthy'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $appStats['status']['healthy'] ? 'Healthy' : 'Offline' }}
                    </span>
                </div>
                <p class="text-xs text-gray-400 mt-1">
                    Last check: {{ $appStats['status']['last_check']->diffForHumans() }}
                </p>
            </div>
            <x-heroicon-o-signal class="w-8 h-8 {{ $appStats['status']['healthy'] ? 'text-green-500' : 'text-red-500' }}" />
        </div>
    </div>

    <!-- Current Connections -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Connections</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($appStats['current_connections']) }}</p>
                <div class="flex items-center space-x-2 mt-1">
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ min($appStats['utilization_percentage'], 100) }}%"></div>
                    </div>
                    <span class="text-xs text-gray-500">{{ $appStats['utilization_percentage'] }}%</span>
                </div>
                @if($debugMode)
                    <p class="text-xs text-blue-500 mt-1">Max: {{ number_format($appStats['max_connections']) }}</p>
                @endif
            </div>
            <x-heroicon-o-users class="w-8 h-8 text-blue-500" />
        </div>
    </div>

    <!-- Events Today -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Events (24h)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($eventStats['events_last_24h']) }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ number_format($eventStats['events_per_hour'], 1) }}/hour
                    @if($eventStats['failed_events'] > 0)
                        | <span class="text-red-500">{{ $eventStats['failed_events'] }} failed</span>
                    @endif
                </p>
            </div>
            <x-heroicon-o-bolt class="w-8 h-8 text-yellow-500" />
        </div>
    </div>

    <!-- Active Channels -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Channels</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($channelStats['active_channels']) }}</p>
                <p class="text-xs text-gray-500 mt-1">of {{ number_format($channelStats['total_channels']) }} total</p>
            </div>
            <x-heroicon-o-chat-bubble-left-ellipsis class="w-8 h-8 text-green-500" />
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <!-- Connection Trend Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            Connection Trend (24h)
            @if($debugMode)
                <span class="text-xs text-gray-500">(Real Data)</span>
            @endif
        </h3>

        <!-- Bar Chart -->
        <div class="h-48 bg-gray-50 dark:bg-gray-900 rounded p-3 relative">
            <div class="flex items-end justify-between h-full space-x-1">
                @php
                    $chartData = $appStats['hourly_data']['data'] ?? [];
                    $chartLabels = $appStats['hourly_data']['labels'] ?? [];
                    $maxValue = max($chartData) ?: 1;
                    $minValue = min($chartData) ?: 0;

                    // Debug chart data
                    if($debugMode) {
                        Log::info("Chart data: " . json_encode($chartData));
                        Log::info("Max value: {$maxValue}, Min value: {$minValue}");
                    }
                @endphp

                @if(!empty($chartData))
                    @for($i = 0; $i < min(count($chartData), 24); $i++)
                        @php
                            $value = $chartData[$i] ?? 0;
                            $percentage = $maxValue > 0 ? (($value - $minValue) / ($maxValue - $minValue)) * 100 : 0;
                            $percentage = max(2, min(100, $percentage)); // Ensure at least 2% height for visibility
                        @endphp
                        <div class="flex flex-col items-center space-y-1 flex-1 min-w-0">
                            <div
                                class="bg-blue-500 rounded-sm w-full transition-all duration-300 hover:bg-blue-600 relative group"
                                style="height: {{ $percentage }}%; min-height: 4px;"
                                title="{{ $value }} connections at {{ $chartLabels[$i] ?? '' }}"
                            >
                                <!-- Tooltip on hover -->
                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">
                                    {{ $value }} at {{ $chartLabels[$i] ?? '' }}
                                </div>
                            </div>
                            @if($i % 4 === 0 && isset($chartLabels[$i]))
                                <span class="text-xs text-gray-500 transform -rotate-45 text-center">
                                    {{ substr($chartLabels[$i], 0, 5) }}
                                </span>
                            @endif
                        </div>
                    @endfor
                @else
                    <div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                        <div class="text-center">
                            <x-heroicon-o-chart-bar class="w-8 h-8 mx-auto mb-2 opacity-50" />
                            <p class="text-sm">No connection data available</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Y-axis labels -->
            @if(!empty($chartData))
                <div class="absolute left-0 top-0 h-full flex flex-col justify-between py-2 text-xs text-gray-500 pointer-events-none">
                    <span>{{ number_format($maxValue) }}</span>
                    <span>{{ number_format(($maxValue + $minValue) / 2) }}</span>
                    <span>{{ number_format($minValue) }}</span>
                </div>
            @endif
        </div>

        <!-- Chart Summary -->
        <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs">
            <div>
                <span class="text-gray-500">Current:</span>
                <span class="font-medium">{{ number_format($appStats['current_connections']) }}</span>
            </div>
            <div>
                <span class="text-gray-500">Peak:</span>
                <span class="font-medium">{{ number_format($appStats['peak_connections_today']) }}</span>
            </div>
            <div>
                <span class="text-gray-500">Avg:</span>
                <span class="font-medium">{{ number_format(array_sum($chartData) / max(count($chartData), 1)) }}</span>
            </div>
        </div>

        @if($debugMode)
            <!-- Debug Chart Data -->
            <div class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900 rounded text-xs">
                <strong>Chart Debug:</strong>
                Data points: {{ count($appStats['hourly_data']['data'] ?? []) }},
                Max: {{ max($appStats['hourly_data']['data'] ?? [0]) }},
                Current: {{ $appStats['current_connections'] }}
                <details class="mt-1">
                    <summary>Raw Data</summary>
                    <pre class="mt-1 text-xs bg-gray-100 dark:bg-gray-800 p-2 rounded overflow-auto max-h-20">{{ json_encode($appStats['hourly_data']['data'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                </details>
            </div>
        @endif
    </div>

    <!-- Event Types -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            Event Types
            @if($debugMode)
                <span class="text-xs text-gray-500">(From Metrics)</span>
            @endif
        </h3>
        <div class="space-y-3">
            @php $totalEvents = array_sum($eventStats['event_types']); @endphp
            @forelse($eventStats['event_types'] as $type => $count)
                @php $percentage = $totalEvents > 0 ? ($count / $totalEvents) * 100 : 0; @endphp
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ str_replace('_', ' ', $type) }}</span>
                    <div class="flex items-center space-x-2">
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-2 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100 w-12 text-right">{{ number_format($count) }}</span>
                        <span class="text-xs text-gray-500 w-10 text-right">{{ round($percentage, 1) }}%</span>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No event data available</p>
                @if($debugMode)
                    <p class="text-xs text-yellow-600 text-center">Check if Soketi is exposing event metrics</p>
                @endif
            @endforelse

            @if($totalEvents > 0)
                <div class="pt-2 border-t border-gray-200 dark:border-gray-700 text-center">
                    <span class="text-xs text-gray-500">Total: {{ number_format($totalEvents) }} events</span>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
    <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-blue-600 dark:text-blue-300">Peak Connections</p>
                <p class="text-xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($appStats['peak_connections_today']) }}</p>
                <p class="text-xs text-blue-500 dark:text-blue-400">Today's maximum</p>
            </div>
            <x-heroicon-o-arrow-trending-up class="w-6 h-6 text-blue-600" />
        </div>
    </div>

    <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900 dark:to-green-800 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-green-600 dark:text-green-300">Avg Duration</p>
                <p class="text-xl font-bold text-green-900 dark:text-green-100">
                    {{ gmdate('H:i:s', $appStats['avg_connection_duration']) }}
                </p>
                <p class="text-xs text-green-500 dark:text-green-400">Per connection</p>
            </div>
            <x-heroicon-o-clock class="w-6 h-6 text-green-600" />
        </div>
    </div>

    <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900 dark:to-purple-800 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-purple-600 dark:text-purple-300">Webhook Calls</p>
                <p class="text-xl font-bold text-purple-900 dark:text-purple-100">{{ number_format($eventStats['webhook_calls']) }}</p>
                <p class="text-xs text-purple-500 dark:text-purple-400">Last 24 hours</p>
            </div>
            <x-heroicon-o-link class="w-6 h-6 text-purple-600" />
        </div>
    </div>
</div>

@if($debugMode)
    <!-- Real-time Data Refresh -->
    <div class="mt-6 text-center">
        <button
            onclick="window.location.reload()"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
        >
            Refresh Real Data
        </button>
        <p class="text-xs text-gray-500 mt-1">Data cached for 30 seconds</p>
    </div>
@endif

<!-- Styles for Charts and Layout -->
<style>
    /* Fixed height container to prevent any growth */
    .h-48 {
        height: 12rem !important;
        max-height: 12rem !important;
        min-height: 12rem !important;
    }

    /* Ensure flex items don't overflow */
    .flex-1 {
        min-width: 0;
        max-width: none;
    }

    /* Force bar chart containers to be equal width */
    .space-x-1 > * {
        flex: 1;
        min-width: 0;
        max-width: none;
    }

    /* Ensure chart bars are visible with minimum height */
    .space-x-1 > div > div:first-child {
        background-color: rgb(59 130 246) !important;
        min-height: 4px !important;
        border-radius: 2px !important;
    }

    /* Improve chart container */
    .h-48.bg-gray-50 {
        min-height: 12rem !important;
        max-height: 12rem !important;
        overflow: hidden !important;
    }

    /* Chart bar hover effects */
    .space-x-1 > div > div:first-child:hover {
        background-color: rgb(37 99 235) !important;
        transform: scaleY(1.05);
        transition: all 0.2s ease;
    }

    /* Ensure proper flexbox behavior */
    .flex.items-end.justify-between.h-full {
        height: 100% !important;
        align-items: flex-end !important;
    }

    @if($debugMode)
        /* Debug mode highlighting */
    .chart-debug {
        border: 2px dashed #3b82f6 !important;
    }

    .chart-debug .space-x-1 > div > div:first-child {
        border: 1px solid #1d4ed8 !important;
    }
    @endif
</style>
