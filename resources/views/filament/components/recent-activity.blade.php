@php
    $statsService = app(\App\Services\SoketiStatsService::class);
    $recentEvents = $statsService->getRecentEvents($app, 20);
    $channelStats = $statsService->getChannelStats($app);
@endphp

<div class="space-y-6">
    <!-- Active Channels -->
    @if(!empty($channelStats['channels']))
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                <x-heroicon-o-chat-bubble-left-ellipsis class="w-5 h-5 mr-2 text-blue-500" />
                Active Channels ({{ count($channelStats['channels']) }})
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach(array_slice($channelStats['channels'], 0, 6) as $channel)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                    {{ $channel['name'] ?? 'Unknown Channel' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $channel['subscription_count'] ?? 0 }} subscribers
                                </p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 rounded-full {{ ($channel['occupied'] ?? false) ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                                <span class="text-xs text-gray-500">
                            {{ ($channel['occupied'] ?? false) ? 'Active' : 'Idle' }}
                        </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if(count($channelStats['channels']) > 6)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 text-center">
                    And {{ count($channelStats['channels']) - 6 }} more channels...
                </p>
            @endif
        </div>
    @endif

    <!-- Recent Events -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                <x-heroicon-o-clock class="w-5 h-5 mr-2 text-green-500" />
                Recent Events
            </h4>
            <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                <span>Live</span>
            </div>
        </div>

        @if(empty($recentEvents))
            <div class="text-center py-8">
                <x-heroicon-o-inbox class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                <p class="text-gray-500 dark:text-gray-400">No recent events</p>
                <p class="text-sm text-gray-400 dark:text-gray-500">Events will appear here when activity occurs</p>
            </div>
        @else
            <div class="space-y-3 max-h-96 overflow-y-auto">
                @foreach($recentEvents as $event)
                    <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <div class="flex-shrink-0">
                            @switch($event['type'])
                                @case('message')
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                        <x-heroicon-o-chat-bubble-left class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    @break
                                @case('member_added')
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <x-heroicon-o-user-plus class="w-4 h-4 text-green-600 dark:text-green-400" />
                                    </div>
                                    @break
                                @case('member_removed')
                                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                        <x-heroicon-o-user-minus class="w-4 h-4 text-red-600 dark:text-red-400" />
                                    </div>
                                    @break
                                @case('channel_occupied')
                                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                        <x-heroicon-o-megaphone class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                                    </div>
                                    @break
                                @default
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                        <x-heroicon-o-bolt class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                                    </div>
                            @endswitch
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ ucfirst(str_replace('_', ' ', $event['type'])) }}
                                </p>
                                <div class="flex items-center space-x-2">
                                    @if(!$event['success'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Failed
                                </span>
                                    @endif
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($event['timestamp'])->diffForHumans() }}
                            </span>
                                </div>
                            </div>

                            <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                        <span class="flex items-center">
                            <x-heroicon-o-hashtag class="w-3 h-3 mr-1" />
                            {{ $event['channel'] }}
                        </span>
                                <span class="flex items-center">
                            <x-heroicon-o-document-text class="w-3 h-3 mr-1" />
                            {{ number_format($event['data_size']) }} bytes
                        </span>
                                <span class="flex items-center">
                            <x-heroicon-o-identification class="w-3 h-3 mr-1" />
                            {{ $event['id'] }}
                        </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Event Statistics Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $eventStats = app(\App\Services\SoketiStatsService::class)->getEventStats($app);
            $successfulEvents = collect($recentEvents)->where('success', true)->count();
            $failedEvents = collect($recentEvents)->where('success', false)->count();
            $successRate = $recentEvents ? round(($successfulEvents / count($recentEvents)) * 100, 1) : 100;
        @endphp

        <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900 dark:to-emerald-900 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-600 dark:text-green-300">Success Rate</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $successRate }}%</p>
                </div>
                <x-heroicon-o-check-circle class="w-8 h-8 text-green-500" />
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900 dark:to-cyan-900 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-600 dark:text-blue-300">Events/Hour</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($eventStats['events_per_hour'], 1) }}</p>
                </div>
                <x-heroicon-o-arrow-trending-up class="w-8 h-8 text-blue-500" />
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-50 to-violet-50 dark:from-purple-900 dark:to-violet-900 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-600 dark:text-purple-300">Webhook Calls</p>
                    <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ number_format($eventStats['webhook_calls']) }}</p>
                </div>
                <x-heroicon-o-link class="w-8 h-8 text-purple-500" />
            </div>
        </div>

        <div class="bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900 dark:to-amber-900 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-orange-600 dark:text-orange-300">Last Event</p>
                    <p class="text-sm font-bold text-orange-900 dark:text-orange-100">
                        {{ $eventStats['last_event_time'] ? $eventStats['last_event_time']->diffForHumans() : 'No events' }}
                    </p>
                </div>
                <x-heroicon-o-clock class="w-8 h-8 text-orange-500" />
            </div>
        </div>
    </div>
</div>

<!-- Auto-refresh script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-refresh this component every 30 seconds
        setInterval(function() {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('$refresh');
            }
        }, 30000);
    });
</script>
