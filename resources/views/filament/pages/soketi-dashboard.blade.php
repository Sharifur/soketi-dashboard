<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Real-time Status Bar -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 rounded-full {{ $serverStatus['online'] ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></div>
                        <span class="text-sm font-medium">
                            Socket Server: {{ $serverStatus['online'] ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-500">
                        Last updated: {{ $serverStatus['last_check']->diffForHumans() }}
                    </div>
                </div>
                <div class="text-xs text-gray-500">
                    Auto-refresh: {{ $refreshInterval }}s
                </div>
            </div>
        </div>

        <!-- Server Information -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Server Port</p>
                        <p class="text-2xl font-bold">6001</p>
                    </div>
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-server class="w-5 h-5 text-blue-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Metrics Port</p>
                        <p class="text-2xl font-bold">9601</p>
                    </div>
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-green-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Memory Usage</p>
                        <p class="text-2xl font-bold">{{ number_format(($metrics['soketi_memory_usage_bytes'] ?? 0) / 1024 / 1024, 1) }}MB</p>
                    </div>
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-cpu-chip class="w-5 h-5 text-yellow-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-lg font-medium mb-4">Quick Actions</h3>
            <div class="flex flex-wrap gap-2">
                <x-filament::button
                    wire:click="$refresh"
                    size="sm"
                    color="primary"
                    icon="heroicon-o-arrow-path"
                >
                    Refresh Metrics
                </x-filament::button>

                <x-filament::button
                    href="http://127.0.0.1:9601/metrics"
                    tag="a"
                    target="_blank"
                    size="sm"
                    color="gray"
                    icon="heroicon-o-document-text"
                >
                    Raw Metrics
                </x-filament::button>

                <x-filament::button
                    wire:navigate
                    href="{{ route('filament.admin.resources.soketi-apps.index') }}"
                    size="sm"
                    color="success"
                    icon="heroicon-o-cog-6-tooth"
                >
                    Manage Apps
                </x-filament::button>
            </div>
        </div>

        <!-- Raw Metrics Display (for debugging) -->
        @if(!empty($metrics))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <details class="group">
                    <summary class="flex items-center justify-between cursor-pointer text-lg font-medium">
                        <span>Raw Metrics Data</span>
                        <x-heroicon-o-chevron-down class="w-5 h-5 group-open:rotate-180 transition-transform" />
                    </summary>
                    <div class="mt-4 bg-gray-50 dark:bg-gray-900 rounded p-4 max-h-96 overflow-auto">
                        <pre class="text-xs text-gray-600 dark:text-gray-400">{{ json_encode($metrics, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </details>
            </div>
        @endif
    </div>

    <!-- Auto-refresh script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-refresh the page every {{ $refreshInterval }} seconds
            setInterval(function() {
                Livewire.dispatch('$refresh');
            }, {{ $refreshInterval * 1000 }});
        });
    </script>
</x-filament-panels::page>
