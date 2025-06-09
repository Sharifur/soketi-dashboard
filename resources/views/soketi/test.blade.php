<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soketi Connection Test - {{ $testResults['app']->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
<div class="max-w-4xl mx-auto px-4">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Soketi Connection Test</h1>
                <p class="text-gray-600 mt-1">Testing connection for: <strong>{{ $testResults['app']->name }}</strong></p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Test Time</p>
                <p class="font-mono text-sm">{{ $testResults['timestamp']->format('Y-m-d H:i:s') }}</p>
            </div>
        </div>
    </div>

    <!-- App Details -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Application Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">App ID</label>
                <p class="mt-1 font-mono text-sm bg-gray-50 p-2 rounded">{{ $testResults['app']->app_id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">App Key</label>
                <p class="mt-1 font-mono text-sm bg-gray-50 p-2 rounded">{{ $testResults['app']->app_key }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Max Connections</label>
                <p class="mt-1 text-sm bg-gray-50 p-2 rounded">{{ $testResults['app']->max_connections === 0 ? 'Unlimited' : number_format($testResults['app']->max_connections) }}</p>
            </div>
        </div>
    </div>

    <!-- Server Test Results -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Server Connection Test</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <div class="mt-1">
                    @if($testResults['server_test']['success'])
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ✓ Connected
                            </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                ✗ Failed
                            </span>
                    @endif
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Response Time</label>
                <p class="mt-1 font-mono text-sm">{{ $testResults['server_test']['response_time'] }}ms</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status Code</label>
                <p class="mt-1 font-mono text-sm">{{ $testResults['server_test']['status_code'] }}</p>
            </div>
        </div>
        @if(!$testResults['server_test']['success'])
            <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-800">
                    <strong>Error:</strong> {{ $testResults['server_test']['message'] }}
                </p>
            </div>
        @endif
    </div>

    <!-- WebSocket Connection Test -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">WebSocket Connection Test</h2>

        <div class="space-y-4">
            <div id="connection-status" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-800">⏳ Initializing WebSocket connection...</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <button id="connect-btn" onclick="testWebSocketConnection()"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    Test WebSocket Connection
                </button>
                <button id="send-test-btn" onclick="sendTestMessage()" disabled
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Send Test Message
                </button>
            </div>
        </div>

        <div id="connection-log" class="mt-4 p-4 bg-gray-50 rounded-lg">
            <h3 class="font-medium text-gray-900 mb-2">Connection Log</h3>
            <div id="log-content" class="text-sm font-mono text-gray-600 max-h-40 overflow-y-auto">
                <p>Ready to test connection...</p>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="text-center">
        <a href="/admin/soketi-apps"
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            ← Back to Dashboard
        </a>
    </div>
</div>

<script>
    let pusher;
    let channel;

    function log(message) {
        const logContent = document.getElementById('log-content');
        const timestamp = new Date().toLocaleTimeString();
        logContent.innerHTML += `<div>[${timestamp}] ${message}</div>`;
        logContent.scrollTop = logContent.scrollHeight;
    }

    function updateStatus(message, type = 'info') {
        const statusEl = document.getElementById('connection-status');
        const colors = {
            info: 'bg-blue-50 border-blue-200 text-blue-800',
            success: 'bg-green-50 border-green-200 text-green-800',
            error: 'bg-red-50 border-red-200 text-red-800',
            warning: 'bg-yellow-50 border-yellow-200 text-yellow-800'
        };

        statusEl.className = `p-4 border rounded-lg ${colors[type] || colors.info}`;
        statusEl.innerHTML = `<p class="text-sm">${message}</p>`;
    }

    function testWebSocketConnection() {
        log('Attempting to connect to Soketi WebSocket...');
        updateStatus('🔄 Connecting to WebSocket...', 'info');

        try {
            pusher = new Pusher('{{ $testResults['app']->app_key }}', {
                wsHost: window.location.hostname,
                wsPort: 6001,
                forceTLS: false,
                disableStats: true,
                enabledTransports: ['ws', 'wss']
            });

            pusher.connection.bind('connected', function() {
                log('✓ Successfully connected to Soketi!');
                updateStatus('✅ WebSocket Connected Successfully!', 'success');
                document.getElementById('send-test-btn').disabled = false;

                // Subscribe to a test channel
                channel = pusher.subscribe('test-channel');
                log('Subscribed to test-channel');
            });

            pusher.connection.bind('error', function(err) {
                log(`✗ Connection error: ${err.error ? err.error.message : 'Unknown error'}`);
                updateStatus('❌ WebSocket Connection Failed', 'error');
            });

            pusher.connection.bind('disconnected', function() {
                log('Connection disconnected');
                updateStatus('⚠️ WebSocket Disconnected', 'warning');
                document.getElementById('send-test-btn').disabled = true;
            });

        } catch (error) {
            log(`✗ Error initializing Pusher: ${error.message}`);
            updateStatus('❌ Failed to Initialize WebSocket', 'error');
        }
    }

    function sendTestMessage() {
        if (!channel) {
            log('✗ No active channel to send message');
            return;
        }

        // In a real app, you'd trigger this from the server
        log('Note: In a real application, messages are triggered from the server');
        log('This is just a client-side connection test');

        // Bind to a test event
        channel.bind('test-event', function(data) {
            log(`✓ Received test event: ${JSON.stringify(data)}`);
        });

        log('Listening for test-event on test-channel...');
    }

    // Auto-start connection test
    setTimeout(testWebSocketConnection, 1000);
</script>
</body>
</html>
