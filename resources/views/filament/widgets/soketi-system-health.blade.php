<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Soketi System Health
        </x-slot>

        <div class="space-y-4">
            <!-- Soketi Status -->
            <div class="flex items-center justify-between p-4 rounded-lg border {{ $soketi_status['success'] == true ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 rounded-full {{ $soketi_status['success'] == true ? 'bg-green-400' : 'bg-red-400' }}"></div>
                    <div>
                        <h3 class="font-medium {{ $soketi_status['success'] == true ? 'text-green-900' : 'text-red-900' }}">
                            Soketi Server
                        </h3>
                        <p class="text-sm {{ $soketi_status['success'] == true ? 'text-green-700' : 'text-red-700' }}">
                            {{ $soketi_status['message'] }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs {{ $soketi_status['success'] == true ? 'text-green-600' : 'text-red-600' }} uppercase font-semibold">
                        {{ $soketi_status['success'] ? 'Online' : 'Offline' }}
                    </span>
                </div>
            </div>

            <!-- Metrics (if available) -->
            @if($soketi_metrics['available'])
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-lg border border-gray-200 bg-gray-50">
                        <h4 class="font-medium text-gray-900 mb-1">Active Connections</h4>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ $soketi_metrics['connections'] ?? 'N/A' }}
                        </p>
                    </div>

                    <div class="p-4 rounded-lg border border-gray-200 bg-gray-50">
                        <h4 class="font-medium text-gray-900 mb-1">Messages Sent</h4>
                        <p class="text-2xl font-bold text-green-600">
                            {{ $soketi_metrics['messages'] ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="flex space-x-2 pt-4 border-t border-gray-200">
                <x-filament::button
                    color="primary"
                    size="sm"
                    wire:click="$refresh"
                >
                    Refresh Status
                </x-filament::button>

                <x-filament::button
                    color="gray"
                    size="sm"
                    href="{{ config('broadcasting.connections.pusher.options.scheme') }}://{{ config('broadcasting.connections.pusher.options.host') }}:{{ config('broadcasting.connections.pusher.options.port') }}"
                    target="_blank"
                    tag="a"
                >
                    Open Soketi
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
