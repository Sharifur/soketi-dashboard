<div class="space-y-4">
    @if(!empty($userData))
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="font-medium text-gray-900 mb-3">User Data</h3>
            <div class="space-y-2">
                @foreach($userData as $key => $value)
                    <div class="flex justify-between items-start">
                        <span class="font-medium text-gray-700 text-sm">{{ $key }}:</span>
                        <span class="text-gray-900 text-sm font-mono ml-4 break-all">
                            @if(is_array($value) || is_object($value))
                                {{ json_encode($value, JSON_PRETTY_PRINT) }}
                            @else
                                {{ $value }}
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        This user data was sent by the client when establishing the WebSocket connection. It can include authentication information, user preferences, or any custom data your application needs.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No user data</h3>
            <p class="mt-1 text-sm text-gray-500">This connection has no associated user data.</p>
        </div>
    @endif
</div>
