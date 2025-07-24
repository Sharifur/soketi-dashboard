{{-- resources/views/filament/components/live-stats.blade.php --}}
<div class="space-y-6">
    <!-- Real-time Status Header -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-900 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                    Live Statistics for {{ $app->app_name ?? $app->id }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Real-time metrics and activity monitoring
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></div>
                <span class="text-sm font-medium text-green-600 dark:text-green-400">Live</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Updated {{ now()->format('H:i:s') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Connections</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($appStats['current_connections']) }}</p>
                    <div class="flex items-center space-x-2 mt-1">
                        <div class="w-16 bg-gray-200 rounded-full h-1">
                            <div class="bg-blue-600 h-1 rounded-full" style="width: {{ $appStats['utilization_percentage'] }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $appStats['utilization_percentage'] }}%</span>
                    </div>
                </div>
                <x-heroicon-o-users class="w-10 h-10 text-blue-500" />
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Events/Minute</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($eventStats['events_per_hour'] / 60, 1) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ number_format($eventStats['events_last_24h']) }} today</p>
                </div>
                <x-heroicon-o-bolt class="w-10 h-10 text-green-500" />
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Channels</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format(count($recentEvents)) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Broadcasting now</p>
                </div>
                <x-heroicon-o-chat-bubble-left-ellipsis class="w-10 h-10 text-purple-500" />
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Success Rate</p>
                    @php
                        $successfulEvents = collect($recentEvents)->where('success', true)->count();
                        $successRate = $recentEvents ? round(($successfulEvents / count($recentEvents)) * 100, 1) : 100;
                    @endphp
                    <p class="text-2xl font-bold text-orange-600">{{ $successRate }}%</p>
                    <p class="text-xs text-gray-500 mt-1">Last 20 events</p>
                </div>
                <x-heroicon-o-check-circle class="w-10 h-10 text-orange-500" />
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Real-time Connection Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <x-heroicon-o-chart-bar class="w-5 h-5 mr-2 text-blue-500" />
                Connection Trend (24h)
            </h3>
            <canvas id="liveConnectionChart" width="400" height="300"></canvas>
        </div>

        <!-- Event Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <x-heroicon-o-chart-pie class="w-5 h-5 mr-2 text-green-500" />
                Event Types Distribution
            </h3>
            <canvas id="eventTypeChart" width="400" height="300"></canvas>
        </div>
    </div>

    <!-- Live Event Stream -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                <x-heroicon-o-bolt class="w-5 h-5 mr-2 text-yellow-500" />
                Live Event Stream
            </h3>
            <div class="flex items-center space-x-3">
                <button id="pauseEvents" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600">
                    Pause
                </button>
                <button id="clearEvents" class="px-3 py-1 text-sm bg-red-200 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-md hover:bg-red-300 dark:hover:bg-red-800">
                    Clear
                </button>
            </div>
        </div>

        <div id="eventStream" class="space-y-2 max-h-64 overflow-y-auto bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
            @foreach(array_slice($recentEvents, 0, 10) as $event)
                <div class="flex items-start space-x-3 text-sm event-item" data-timestamp="{{ $event['timestamp'] }}">
                <span class="text-gray-500 dark:text-gray-400 font-mono text-xs">
                    {{ \Carbon\Carbon::parse($event['timestamp'])->format('H:i:s') }}
                </span>
                    <span class="font-medium {{ $event['success'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ ucfirst(str_replace('_', ' ', $event['type'])) }}
                </span>
                    <span class="text-gray-600 dark:text-gray-300">{{ $event['channel'] }}</span>
                    <span class="text-gray-500 dark:text-gray-400">{{ number_format($event['data_size']) }}B</span>
                    @if(!$event['success'])
                        <span class="text-red-500 dark:text-red-400 text-xs">✗ FAILED</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 rounded-lg p-4">
            <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Peak Performance</h4>
            <p class="text-2xl font-bold text-blue-700 dark:text-blue-200">{{ number_format($appStats['peak_connections_today']) }}</p>
            <p class="text-sm text-blue-600 dark:text-blue-300">Max connections today</p>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900 dark:to-green-800 rounded-lg p-4">
            <h4 class="font-medium text-green-900 dark:text-green-100 mb-2">Average Duration</h4>
            <p class="text-2xl font-bold text-green-700 dark:text-green-200">
                {{ gmdate('i:s', $appStats['avg_connection_duration']) }}
            </p>
            <p class="text-sm text-green-600 dark:text-green-300">Per connection</p>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900 dark:to-purple-800 rounded-lg p-4">
            <h4 class="font-medium text-purple-900 dark:text-purple-100 mb-2">Data Transfer</h4>
            @php
                $totalBytes = collect($recentEvents)->sum('data_size');
                $formattedBytes = $totalBytes > 1024 ? round($totalBytes / 1024, 1) . 'KB' : $totalBytes . 'B';
            @endphp
            <p class="text-2xl font-bold text-purple-700 dark:text-purple-200">{{ $formattedBytes }}</p>
            <p class="text-sm text-purple-600 dark:text-purple-300">Recent events</p>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let eventsPaused = false;

        // Connection Chart
        const connectionCtx = document.getElementById('liveConnectionChart').getContext('2d');
        const connectionChart = new Chart(connectionCtx, {
            type: 'line',
            data: {
                labels: @json($appStats['hourly_data']['labels']),
                datasets: [{
                    label: 'Connections',
                    data: @json($appStats['hourly_data']['data']),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Event Type Chart
        const eventTypeCtx = document.getElementById('eventTypeChart').getContext('2d');
        const eventTypeChart = new Chart(eventTypeCtx, {
            type: 'doughnut',
            data: {
                labels: @json(array_keys($eventStats['event_types'])),
                datasets: [{
                    data: @json(array_values($eventStats['event_types'])),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 101, 101, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Event stream controls
        document.getElementById('pauseEvents').addEventListener('click', function() {
            eventsPaused = !eventsPaused;
            this.textContent = eventsPaused ? 'Resume' : 'Pause';
            this.className = eventsPaused ?
                'px-3 py-1 text-sm bg-green-200 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-md hover:bg-green-300 dark:hover:bg-green-800' :
                'px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600';
        });

        document.getElementById('clearEvents').addEventListener('click', function() {
            document.getElementById('eventStream').innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-center py-4">Event stream cleared</p>';
        });

        // Simulate real-time updates
        function updateCharts() {
            if (!eventsPaused) {
                // Add new data point to connection chart
                const now = new Date();
                const timeLabel = now.getHours().toString().padStart(2, '0') + ':' +
                    now.getMinutes().toString().padStart(2, '0');

                connectionChart.data.labels.push(timeLabel);
                connectionChart.data.datasets[0].data.push(Math.floor(Math.random() * 20) + {{ $appStats['current_connections'] }} - 10);

                if (connectionChart.data.labels.length > 24) {
                    connectionChart.data.labels.shift();
                    connectionChart.data.datasets[0].data.shift();
                }

                connectionChart.update('none');
            }
        }

        // Update every 15 seconds
        setInterval(updateCharts, 15000);
    });
</script>
