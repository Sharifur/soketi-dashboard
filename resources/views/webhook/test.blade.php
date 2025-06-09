<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webhook Test - {{ $webhook->event_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 min-h-screen py-8">
<div class="max-w-4xl mx-auto px-4">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Webhook Test</h1>
                <p class="text-gray-600 mt-1">Testing webhook delivery for: <strong>{{ $webhook->event_name }}</strong></p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Application</p>
                <p class="font-semibold">{{ $webhook->soketiApp->name }}</p>
            </div>
        </div>
    </div>

    <!-- Webhook Details -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Webhook Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Event Type</label>
                <p class="mt-1 text-sm bg-gray-50 p-2 rounded">{{ $webhook->event_name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Current Status</label>
                <p class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $webhook->status === 'sent' ? 'bg-green-100 text-green-800' : ($webhook->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($webhook->status) }}
                        </span>
                </p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Webhook URL</label>
                <p class="mt-1 font-mono text-sm bg-gray-50 p-2 rounded break-all">{{ $webhook->webhook_url }}</p>
            </div>
        </div>
    </div>

    <!-- Test Results -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Test Results</h2>

        <div id="test-status" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-sm text-blue-800">⏳ Ready to test webhook delivery</p>
        </div>

        <div class="flex space-x-4 mb-6">
            <button id="test-btn" onclick="testWebhook()"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                Send Test Webhook
            </button>
            <button onclick="location.reload()"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                Reset Test
            </button>
        </div>

        <div id="test-results" class="hidden">
            <h3 class="font-medium text-gray-900 mb-3">Response Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status Code</label>
                    <p id="response-status" class="mt-1 font-mono text-sm"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Response Time</label>
                    <p id="response-time" class="mt-1 font-mono text-sm"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Content Length</label>
                    <p id="content-length" class="mt-1 font-mono text-sm"></p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Response Body</label>
                <div id="response-body" class="mt-1 bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto">
                    <pre class="text-sm font-mono whitespace-pre-wrap"></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Payload Preview -->
    @if(!empty($webhook->payload))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payload Being Sent</h2>
            <div class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto">
                <pre class="text-sm font-mono whitespace-pre-wrap">{{ json_encode($webhook->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>
    @endif

    <!-- Headers Preview -->
    @if(!empty($webhook->headers))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">HTTP Headers</h2>
            <div class="bg-gray-50 rounded border">
                @foreach($webhook->headers as $key => $value)
                    <div class="flex justify-between items-center px-3 py-2 {{ !$loop->last ? 'border-b' : '' }}">
                        <span class="font-medium text-gray-700 text-sm">{{ $key }}:</span>
                        <span class="text-gray-900 text-sm font-mono ml-4 break-all">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- cURL Example -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">cURL Equivalent</h2>
        <div class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto">
                <pre class="text-sm font-mono whitespace-pre-wrap">curl -X POST {{ $webhook->webhook_url }} \
@if(!empty($webhook->headers))
                        @foreach($webhook->headers as $key => $value)
                            -H "{{ $key }}: {{ $value }}" \
                        @endforeach
                    @endif
                    @if(!empty($webhook->payload))
                        -d '{{ json_encode($webhook->payload, JSON_UNESCAPED_SLASHES) }}'
                    @endif</pre>
        </div>
        <button onclick="copyCurl()" class="mt-3 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm transition-colors">
            Copy cURL Command
        </button>
    </div>

    <!-- Back Button -->
    <div class="text-center">
        <a href="/admin/soketi-webhooks"
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            ← Back to Webhooks
        </a>
    </div>
</div>

<script>
    // Set up CSRF token for requests
    fetch.defaults = {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    };

    async function testWebhook() {
        const testBtn = document.getElementById('test-btn');
        const testStatus = document.getElementById('test-status');
        const testResults = document.getElementById('test-results');

        // Disable button and show loading
        testBtn.disabled = true;
        testBtn.textContent = 'Sending...';
        testStatus.className = 'mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg';
        testStatus.innerHTML = '<p class="text-sm text-blue-800">🔄 Sending webhook...</p>';

        try {
            const response = await fetch(`/api/webhook/test/{{ $webhook->id }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();

            // Update status
            if (result.status === 'success') {
                testStatus.className = 'mb-4 p-4 bg-green-50 border border-green-200 rounded-lg';
                testStatus.innerHTML = '<p class="text-sm text-green-800">✅ ' + result.message + '</p>';
            } else {
                testStatus.className = 'mb-4 p-4 bg-red-50 border border-red-200 rounded-lg';
                testStatus.innerHTML = '<p class="text-sm text-red-800">❌ ' + result.message + '</p>';
            }

            // Show results
            testResults.classList.remove('hidden');
            document.getElementById('response-status').textContent = result.response_status;
            document.getElementById('response-time').textContent = Math.round(result.response_time) + 'ms';
            document.getElementById('content-length').textContent = result.response_body ? result.response_body.length + ' bytes' : '0 bytes';
            document.getElementById('response-body').querySelector('pre').textContent = result.response_body || 'No response body';

        } catch (error) {
            testStatus.className = 'mb-4 p-4 bg-red-50 border border-red-200 rounded-lg';
            testStatus.innerHTML = '<p class="text-sm text-red-800">❌ Error: ' + error.message + '</p>';
        } finally {
            // Re-enable button
            testBtn.disabled = false;
            testBtn.textContent = 'Send Test Webhook';
        }
    }

    function copyCurl() {
        const curl = `curl -X POST {{ $webhook->webhook_url }} \\
@if(!empty($webhook->headers))
        @foreach($webhook->headers as $key => $value)
        -H "{{ $key }}: {{ $value }}" \\
@endforeach
        @endif
        @if(!empty($webhook->payload))
        -d '{{ json_encode($webhook->payload, JSON_UNESCAPED_SLASHES) }}'
@endif`;

        navigator.clipboard.writeText(curl).then(function() {
            alert('cURL command copied to clipboard!');
        });
    }
</script>
</body>
</html>
