<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <span class="font-medium">Event Type:</span>
            <span class="ml-2">{{ $record->event_type }}</span>
        </div>
        <div>
            <span class="font-medium">Timestamp:</span>
            <span class="ml-2">{{ $record->timestamp->format('Y-m-d H:i:s') }}</span>
        </div>
        <div>
            <span class="font-medium">Application:</span>
            <span class="ml-2">{{ $record->soketiApp->app_name }}</span>
        </div>
        <div>
            <span class="font-medium">Socket ID:</span>
            <span class="ml-2">{{ $record->socket_id }}</span>
        </div>
        <div>
            <span class="font-medium">Channel:</span>
            <span class="ml-2">{{ $record->channel }}</span>
        </div>
        <div>
            <span class="font-medium">Event:</span>
            <span class="ml-2">{{ $record->event_name }}</span>
        </div>
    </div>

    @if($record->payload)
        <div>
            <span class="font-medium">Payload:</span>
            <pre class="mt-2 p-4 bg-gray-100 rounded-lg overflow-x-auto">{{ json_encode($record->payload, JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endif
</div>
