<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Application Status
        </x-slot>

        <div class="space-y-4">
            @forelse($apps as $app)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-2 h-2 rounded-full {{ $app['enabled'] ? 'bg-green-500' : 'bg-red-500' }}"></div>
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                    App ID: {{ $app['id'] }}
                                </h4>
                                <p class="text-sm text-gray-500">
                                    Key: {{ Str::mask($app['key'], '*', 4, -4) }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $app['enabled'] ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' }}">
                                {{ $app['enabled'] ? 'Active' : 'Disabled' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Max Connections</p>
                            <p class="font-medium">{{ number_format($app['maxConnections'] ?? $app['max_connections'] ?? 1000) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Client Messages</p>
                            <p class="font-medium">{{ ($app['enableClientMessages'] ?? $app['enable_client_messages'] ?? true) ? 'Enabled' : 'Disabled' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Backend Events/sec</p>
                            <p class="font-medium">{{ $app['maxBackendEventsPerSecond'] ?? $app['max_backend_events_per_second'] ?? 100 }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Webhooks</p>
                            <p class="font-medium">{{ count($app['webhooks'] ?? []) }}</p>
                        </div>
                    </div>

                    @if(!empty($app['webhooks']))
                        <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <p class="text-xs text-gray-500 mb-2">Webhook URLs:</p>
                            <div class="space-y-1">
                                @foreach($app['webhooks'] as $webhook)
                                    <div class="flex items-center space-x-2 text-xs">
                                        <x-heroicon-o-link class="w-3 h-3 text-gray-400" />
                                        <span class="font-mono text-gray-600 dark:text-gray-400">{{ $webhook['url'] ?? 'N/A' }}</span>
                                        @if(isset($webhook['event_types']))
                                            <span class="text-gray-500">({{ implode(', ', $webhook['event_types']) }})</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-8">
                    <x-heroicon-o-squares-2x2 class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                    <p class="text-gray-500">No applications configured</p>
                    <p class="text-sm text-gray-400 mt-1">Configure apps in your Laravel API</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
