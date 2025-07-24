<div class="space-y-6">
    {{-- Event Header --}}
    <div class="flex items-center space-x-4">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            @if($event->event_type === 'connection') bg-green-100 text-green-800
            @elseif($event->event_type === 'disconnection') bg-red-100 text-red-800
            @elseif($event->event_type === 'subscription') bg-blue-100 text-blue-800
            @elseif($event->event_type === 'unsubscription') bg-orange-100 text-orange-800
            @elseif($event->event_type === 'message') bg-purple-100 text-purple-800
            @else bg-gray-100 text-gray-800 @endif">
            {{ ucfirst($event->event_type) }}
        </span>

        <div class="text-sm text-gray-500">
            {{ $event->timestamp->format('M j, Y \a\t g:i:s A') }}
            <span class="text-xs">({{ $event->timestamp->diffForHumans() }})</span>
        </div>
    </div>

    {{-- Event Information --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
            <h4 class="font-medium text-gray-900">Event Information</h4>

            <div class="space-y-3">
                @if($event->channel)
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-700">Channel:</span>
                        <span class="text-sm text-gray-900 font-mono">{{ $event->channel }}</span>
                    </div>
                @endif

                @if($event->event_name)
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-700">Event Name:</span>
                        <span class="text-sm text-gray-900 font-mono">{{ $event->event_name }}</span>
                    </div>
                @endif

                @if($event->socket_id)
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-700">Socket ID:</span>
                        <span class="text-sm text-gray-900 font-mono">{{ $event->socket_id }}</span>
                    </div>
                @endif

                @if($event->user_id)
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-700">User ID:</span>
                        <span class="text-sm text-gray-900 font-mono">{{ $event->user_id }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <h4 class="font-medium text-gray-900">Application</h4>

            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-700">App Name:</span>
                    <span class="text-sm text-gray-900">{{ $event->soketiApp->app_name }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-sm font-medium text-gray-700">App ID:</span>
                    <span class="text-sm text-gray-900 font-mono">{{ $event->app_id }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Event Payload --}}
    @if($event->payload && !empty($event->payload))
        <div class="space-y-4">
            <h4 class="font-medium text-gray-900">Event Payload</h4>

            <div class="bg-gray-50 rounded-lg p-4 overflow-auto">
                <pre class="text-sm text-gray-800 whitespace-pre-wrap"><code>{{ json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
        </div>
    @endif

    {{-- Raw Event Data --}}
    <div class="space-y-4">
        <details class="group">
            <summary class="cursor-pointer font-medium text-gray-900 hover:text-gray-700">
                Raw Event Data
                <span class="ml-2 transform transition-transform group-open:rotate-90">▶</span>
            </summary>

            <div class="mt-4 bg-gray-50 rounded-lg p-4 overflow-auto">
                <pre class="text-xs text-gray-700 whitespace-pre-wrap"><code>{{ json_encode($event->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
        </details>
    </div>
</div>
