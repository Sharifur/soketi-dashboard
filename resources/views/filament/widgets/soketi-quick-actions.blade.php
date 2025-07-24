<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Soketi Quick Actions
        </x-slot>

        <div class="space-y-6">
            <!-- Primary Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-lg border border-gray-200 bg-gradient-to-br from-blue-50 to-blue-100">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Test Message</h3>
                            <p class="text-sm text-gray-600">Send a test message</p>
                        </div>
                    </div>
                    <x-filament::button
                        color="primary"
                        size="sm"
                        wire:click="sendTestMessage"
                        class="w-full"
                    >
                        Send Test
                    </x-filament::button>
                </div>

                <div class="p-4 rounded-lg border border-gray-200 bg-gradient-to-br from-green-50 to-green-100">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Health Check</h3>
                            <p class="text-sm text-gray-600">Check server status</p>
                        </div>
                    </div>
                    <x-filament::button
                        color="success"
                        size="sm"
                        wire:click="checkSoketiStatus"
                        class="w-full"
                    >
                        Check Status
                    </x-filament::button>
                </div>

                <div class="p-4 rounded-lg border border-gray-200 bg-gradient-to-br from-purple-50 to-purple-100">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Broadcast</h3>
                            <p class="text-sm text-gray-600">Send to all clients</p>
                        </div>
                    </div>
                    <x-filament::button
                        color="warning"
                        size="sm"
                        wire:click="broadcastToAll"
                        class="w-full"
                    >
                        Broadcast
                    </x-filament::button>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="font-medium text-gray-900 mb-4">Quick Links</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    {{--                    <x-filament::button--}}
                    {{--                        color="gray"--}}
                    {{--                        size="sm"--}}
                    {{--                        href="{{ $soketi_url ?? '' }}"--}}
                    {{--                        target="_blank"--}}
                    {{--                        tag="a"--}}
                    {{--                        class="justify-center"--}}
                    {{--                    >--}}
                    {{--                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">--}}
                    {{--                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>--}}
                    {{--                        </svg>--}}
                    {{--                        Soketi Server--}}
                    {{--                    </x-filament::button>--}}

                    {{--                    <x-filament::button--}}
                    {{--                        color="gray"--}}
                    {{--                        size="sm"--}}
                    {{--                        href="{{ $metrics_url ?? '' }}"--}}
                    {{--                        target="_blank"--}}
                    {{--                        tag="a"--}}
                    {{--                        class="justify-center"--}}
                    {{--                    >--}}
                    {{--                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">--}}
                    {{--                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>--}}
                    {{--                        </svg>--}}
                    {{--                        Metrics--}}
                    {{--                    </x-filament::button>--}}

                    <x-filament::button
                        color="gray"
                        size="sm"
                        wire:click="$refresh"
                        class="justify-center"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </x-filament::button>

                    <x-filament::button
                        color="gray"
                        size="sm"
                        href="/api/soketi/test"
                        target="_blank"
                        tag="a"
                        class="justify-center"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        API Test
                    </x-filament::button>
                </div>
            </div>

            <!-- Current Configuration -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="font-medium text-gray-900 mb-4">Current Configuration</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Server URL</div>
                        <div class="text-sm font-mono text-gray-900 mt-1">{{ $soketi_url }}</div>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">WebSocket URL</div>
                        <div class="text-sm font-mono text-gray-900 mt-1">{{ $websocket_url }}</div>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">App ID</div>
                        <div class="text-sm font-mono text-gray-900 mt-1">{{ env('PUSHER_APP_ID') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
