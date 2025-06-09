{{-- Create this file at: resources/views/filament/modals/webhook-payload.blade.php --}}

<div class="space-y-6">
    {{-- Webhook Information --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Webhook Information</h3>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Event Type</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ $webhook->event_name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                <dd class="text-sm">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($webhook->status === 'sent') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                        @elseif($webhook->status === 'failed') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100 @endif">
                        {{ ucfirst($webhook->status) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Webhook URL</dt>
                <dd class="text-sm text-gray-900 dark:text-white font-mono break-all">{{ $webhook->webhook_url }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Attempts</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ $webhook->attempts ?? 0 }}</dd>
            </div>
        </dl>
    </div>

    {{-- Request Headers --}}
    @if($webhook->headers && !empty($webhook->headers))
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Request Headers</h4>
            <div class="bg-white dark:bg-gray-900 rounded border p-3">
                <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ json_encode($webhook->headers, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    @endif

    {{-- Payload Data --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Payload Data</h4>
        <div class="bg-white dark:bg-gray-900 rounded border p-3">
            @if($webhook->payload && !empty($webhook->payload))
                <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ json_encode($webhook->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 italic">No payload data</p>
            @endif
        </div>
    </div>

    {{-- Response Information --}}
    @if($webhook->response_status || $webhook->response_body)
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Server Response</h4>

            @if($webhook->response_status)
                <div class="mb-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Code: </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium font-mono
                @if($webhook->response_status >= 200 && $webhook->response_status < 300) bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                @elseif($webhook->response_status >= 400 && $webhook->response_status < 500) bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                @elseif($webhook->response_status >= 500) bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                @else bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100 @endif">
                {{ $webhook->response_status }}
            </span>
                </div>
            @endif

            @if($webhook->response_body)
                <div class="bg-white dark:bg-gray-900 rounded border p-3">
                    <pre class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $webhook->response_body }}</pre>
                </div>
            @endif
        </div>
    @endif

    {{-- Timestamps --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Timestamps</h4>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                <dd class="text-sm text-gray-900 dark:text-white">
                    {{ $webhook->created_at ? $webhook->created_at->format('Y-m-d H:i:s') : 'Unknown' }}
                    @if($webhook->created_at)
                        <span class="text-gray-500 dark:text-gray-400">({{ $webhook->created_at->diffForHumans() }})</span>
                    @endif
                </dd>
            </div>
            @if($webhook->sent_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Sent</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">
                        {{ $webhook->sent_at->format('Y-m-d H:i:s') }}
                        <span class="text-gray-500 dark:text-gray-400">({{ $webhook->sent_at->diffForHumans() }})</span>
                    </dd>
                </div>
            @endif
            @if($webhook->next_retry_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Next Retry</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">
                        {{ $webhook->next_retry_at->format('Y-m-d H:i:s') }}
                        <span class="text-gray-500 dark:text-gray-400">({{ $webhook->next_retry_at->diffForHumans() }})</span>
                    </dd>
                </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Updated</dt>
                <dd class="text-sm text-gray-900 dark:text-white">
                    {{ $webhook->updated_at ? $webhook->updated_at->format('Y-m-d H:i:s') : 'Unknown' }}
                    @if($webhook->updated_at)
                        <span class="text-gray-500 dark:text-gray-400">({{ $webhook->updated_at->diffForHumans() }})</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</div>
