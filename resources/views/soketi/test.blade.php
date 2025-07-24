<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Soketi Connection Test - {{ $testResults['app']->app_name ?? 'Test App' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
<div class="max-w-4xl mx-auto px-4">
    <!-- Debug Info -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <h3 class="font-medium text-yellow-800 mb-2">🐛 Debug Information</h3>
        <div class="text-sm space-y-1">
            <p><strong>App ID:</strong> {{ $testResults['app']->id ?? 'N/A' }}</p>
            <p><strong>App Key:</strong> {{ $testResults['app']->key ?? 'N/A' }}</p>
            <p><strong>Host:</strong> {{ config('broadcasting.connections.pusher.options.host', '127.0.0.1') }}</p>
            <p><strong>Port:</strong> {{ config('broadcasting.connections.pusher.options.port', '6001') }}</p>
            <p><strong>Scheme:</strong> {{ config('broadcasting.connections.pusher.options.scheme', 'http') }}</p>
            <p><strong>Encrypted:</strong> {{ config('broadcasting.connections.pusher.options.encrypted', false) ? 'true' : 'false' }}</p>
            <p><strong>Cluster:</strong> {{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}</p>
        </div>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Soketi Connection Test</h1>
                <p class="text-gray-600 mt-1">Testing connection for: <strong>{{ $testResults['app']->app_name ?? 'Unknown App' }}</strong></p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Test Time</p>
                <p class="font-mono text-sm">{{ $testResults['timestamp']->format('Y-m-d H:i:s') }}</p>
            </div>
        </div>
    </div>

    <!-- Configuration Validation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Configuration Validation</h2>
        <div class="space-y-2">
            <div class="flex items-center">
                <span class="w-4 h-4 mr-2">{{ $testResults['app']->id ? '✅' : '❌' }}</span>
                <span class="text-sm">App ID: {{ $testResults['app']->id ? 'Valid' : 'Missing' }}</span>
            </div>
            <div class="flex items-center">
                <span class="w-4 h-4 mr-2">{{ $testResults['app']->key ? '✅' : '❌' }}</span>
                <span class="text-sm">App Key: {{ $testResults['app']->key ? 'Valid' : 'Missing' }}</span>
            </div>
            <div class="flex items-center">
                <span class="w-4 h-4 mr-2">{{ config('broadcasting.connections.pusher.options.host') ? '✅' : '❌' }}</span>
                <span class="text-sm">Host: {{ config('broadcasting.connections.pusher.options.host') ? 'Configured' : 'Missing' }}</span>
            </div>
            <div class="flex items-center">
                <span class="w-4 h-4 mr-2">{{ config('broadcasting.connections.pusher.options.port') ? '✅' : '❌' }}</span>
                <span class="text-sm">Port: {{ config('broadcasting.connections.pusher.options.port') ? 'Configured' : 'Missing' }}</span>
            </div>
        </div>
    </div>

    <!-- Server Test Results -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Server Connection Test</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <div class="mt-1" id="server-status">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        ⏳ Testing...
                    </span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Response Time</label>
                <p class="mt-1 font-mono text-sm" id="response-time">-</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status Code</label>
                <p class="mt-1 font-mono text-sm" id="status-code">-</p>
            </div>
        </div>
        <div id="server-error" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg hidden">
            <p class="text-sm text-red-800">
                <strong>Error:</strong> <span id="server-error-message"></span>
            </p>
        </div>
    </div>

    <!-- WebSocket Connection Test -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">WebSocket Connection Test</h2>

        <div class="space-y-4">
            <div id="connection-status" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-800">⏳ Initializing WebSocket connection...</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <button id="connect-btn" onclick="testWebSocketConnection()"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    Test WebSocket Connection
                </button>
                <button id="send-test-btn" onclick="sendTestMessage()" disabled
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Send Test Message
                </button>
                <button id="trigger-server-btn" onclick="triggerServerMessage()" disabled
                        class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Trigger Server Message
                </button>
                <button id="channel-info-btn" onclick="getChannelInfo()" disabled
                        class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Get Channel Info
                </button>
            </div>
        </div>

        <div id="connection-log" class="mt-4 p-4 bg-gray-50 rounded-lg">
            <h3 class="font-medium text-gray-900 mb-2">Connection Log</h3>
            <div id="log-content" class="text-sm font-mono text-gray-600 max-h-96 overflow-y-auto">
                <p>Ready to test connection...</p>
            </div>
        </div>
    </div>

    <!-- API Test Results -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">API Test Results</h2>
        <div class="space-y-4">
            <button onclick="testApiEndpoint()"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg transition-colors">
                Test API Endpoint
            </button>
            <div id="api-results" class="hidden p-4 rounded-lg border">
                <h4 class="font-medium mb-2">API Response:</h4>
                <pre id="api-response" class="text-xs bg-gray-100 p-2 rounded overflow-x-auto"></pre>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="text-center">
        <a href="{{ '/admin' }}"
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
            ← Back to Dashboard
        </a>
    </div>
</div>

<script>
    let pusher;
    let channel;

    // Configuration from Laravel
    const CONFIG = {
        APP_ID: '{{ $testResults["app"]->id ?? "" }}',
        APP_KEY: '{{ $testResults["app"]->key ?? "" }}',
        HOST: '{{ config("broadcasting.connections.pusher.options.host", "127.0.0.1") }}',
        PORT: {{ config('broadcasting.connections.pusher.options.port', 6001) }},
        SCHEME: '{{ config("broadcasting.connections.pusher.options.scheme", "http") }}',
        ENCRYPTED: {{ config('broadcasting.connections.pusher.options.encrypted', false) ? 'true' : 'false' }},
        CLUSTER: '{{ config("broadcasting.connections.pusher.options.cluster", "mt1") }}'
    };

    // Debug configuration
    function debugConfig() {
        log('🔧 Configuration Debug:');
        log(`APP_ID: ${CONFIG.APP_ID || 'MISSING'}`);
        log(`APP_KEY: ${CONFIG.APP_KEY || 'MISSING'}`);
        log(`HOST: ${CONFIG.HOST}`);
        log(`PORT: ${CONFIG.PORT}`);
        log(`SCHEME: ${CONFIG.SCHEME}`);
        log(`ENCRYPTED: ${CONFIG.ENCRYPTED}`);
        log(`CLUSTER: ${CONFIG.CLUSTER}`);

        // Validate required fields
        const errors = [];
        if (!CONFIG.APP_ID) errors.push('APP_ID is missing');
        if (!CONFIG.APP_KEY) errors.push('APP_KEY is missing');
        if (!CONFIG.HOST) errors.push('HOST is missing');
        if (!CONFIG.PORT) errors.push('PORT is missing');

        if (errors.length > 0) {
            log('❌ Configuration Errors:');
            errors.forEach(error => log(`   - ${error}`));
            return false;
        }

        log('✅ Configuration appears valid');
        return true;
    }

    function log(message) {
        const logContent = document.getElementById('log-content');
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = document.createElement('div');
        logEntry.innerHTML = `[${timestamp}] ${message}`;
        logContent.appendChild(logEntry);
        logContent.scrollTop = logContent.scrollHeight;

        // Also log to console for debugging
        console.log(`[Soketi Test] ${message}`);
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

    function enableButtons() {
        document.getElementById('send-test-btn').disabled = false;
        document.getElementById('trigger-server-btn').disabled = false;
        document.getElementById('channel-info-btn').disabled = false;
    }

    function disableButtons() {
        document.getElementById('send-test-btn').disabled = true;
        document.getElementById('trigger-server-btn').disabled = true;
        document.getElementById('channel-info-btn').disabled = true;
    }

    // Test server connection first
    function testServerConnection() {
        const startTime = Date.now();
        const serverUrl = `${CONFIG.SCHEME}://${CONFIG.HOST}:${CONFIG.PORT}`;

        log(`🔗 Testing server connection to: ${serverUrl}`);

        fetch(serverUrl, {
            method: 'GET',
            mode: 'cors',
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(response => {
                const responseTime = Date.now() - startTime;
                document.getElementById('response-time').textContent = `${responseTime}ms`;
                document.getElementById('status-code').textContent = response.status;

                log(`📊 Server response: ${response.status} (${responseTime}ms)`);

                if (response.ok || response.status === 200) {
                    document.getElementById('server-status').innerHTML = `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            ✓ Connected
                        </span>
                    `;
                    log(`✅ Server connection successful`);
                } else {
                    throw new Error(`Server returned status ${response.status}`);
                }
            })
            .catch(error => {
                const responseTime = Date.now() - startTime;
                document.getElementById('response-time').textContent = `${responseTime}ms`;
                document.getElementById('status-code').textContent = 'Error';

                document.getElementById('server-status').innerHTML = `
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        ✗ Failed
                    </span>
                `;
                document.getElementById('server-error-message').textContent = error.message;
                document.getElementById('server-error').classList.remove('hidden');
                log(`❌ Server connection failed: ${error.message}`);
            });
    }

    function testWebSocketConnection() {
        log('🚀 Starting WebSocket connection test...');

        // First validate configuration
        if (!debugConfig()) {
            updateStatus('❌ Configuration validation failed - check debug log', 'error');
            return;
        }

        updateStatus('🔄 Connecting to WebSocket...', 'info');

        try {
            // Configure Pusher
            const pusherConfig = {
                cluster: CONFIG.CLUSTER,
                forceTLS: CONFIG.ENCRYPTED,
                disableStats: true,
                enabledTransports: ['ws', 'wss'],
                activityTimeout: 30000,
                pongTimeout: 10000
            };

            // Add host configuration for local development
            if (CONFIG.HOST !== 'api.pusherapp.com') {
                pusherConfig.wsHost = CONFIG.HOST;
                pusherConfig.wsPort = CONFIG.PORT;
                pusherConfig.wssPort = CONFIG.PORT;
                pusherConfig.enabledTransports = [CONFIG.SCHEME === 'https' ? 'wss' : 'ws'];

                // For local development, disable TLS verification
                if (CONFIG.HOST === '127.0.0.1' || CONFIG.HOST === '127.0.0.1') {
                    pusherConfig.forceTLS = false;
                }
            }

            log(`🔧 Pusher config: ${JSON.stringify(pusherConfig, null, 2)}`);

            // Initialize Pusher
            if (typeof Pusher === 'undefined') {
                throw new Error('Pusher library not loaded');
            }

            pusher = new Pusher(CONFIG.APP_KEY, pusherConfig);

            // Bind connection events with detailed logging
            pusher.connection.bind('connecting', function() {
                log('🔄 WebSocket connecting...');
                updateStatus('🔄 WebSocket connecting...', 'info');
            });

            pusher.connection.bind('connected', function() {
                const socketId = pusher.connection.socket_id;
                log(`✅ WebSocket connected successfully! Socket ID: ${socketId}`);
                updateStatus(`✅ WebSocket Connected! Socket ID: ${socketId}`, 'success');
                enableButtons();

                // Subscribe to test channel
                try {
                    channel = pusher.subscribe('test-channel');
                    log('📡 Subscribed to test-channel');

                    // Bind to subscription events
                    channel.bind('pusher:subscription_succeeded', function() {
                        log('✅ Successfully subscribed to test-channel');
                    });

                    channel.bind('pusher:subscription_error', function(error) {
                        log(`❌ Subscription error: ${JSON.stringify(error)}`);
                    });

                    // Bind to test events
                    channel.bind('test-event', function(data) {
                        log(`📨 Received test-event: ${JSON.stringify(data)}`);
                    });

                    channel.bind('server-message', function(data) {
                        log(`📨 Received server-message: ${JSON.stringify(data)}`);
                    });

                } catch (subscribeError) {
                    log(`❌ Error subscribing to channel: ${subscribeError.message}`);
                }
            });

            pusher.connection.bind('error', function(err) {
                log(`❌ WebSocket connection error: ${JSON.stringify(err)}`);
                const errorMsg = err && err.error ? err.error.message : (typeof err === 'string' ? err : JSON.stringify(err));
                // log(`❌ Connection error: ${errorMsg}`);
                updateStatus(`❌ Connection Error: ${errorMsg}`, 'error');
                disableButtons();
            });

            pusher.connection.bind('disconnected', function() {
                log('⚠️ WebSocket disconnected');
                updateStatus('⚠️ WebSocket Disconnected', 'warning');
                disableButtons();
            });

            pusher.connection.bind('unavailable', function() {
                log('❌ WebSocket connection unavailable');
                updateStatus('❌ WebSocket Unavailable', 'error');
                disableButtons();
            });

            pusher.connection.bind('failed', function() {
                log('❌ WebSocket connection failed');
                updateStatus('❌ WebSocket Connection Failed', 'error');
                disableButtons();
            });

            // Add state change logging
            pusher.connection.bind('state_change', function(states) {
                log(`🔄 Connection state changed: ${states.previous} → ${states.current}`);
            });

        } catch (error) {
            log(`❌ Error initializing Pusher: ${error.message}`);
            updateStatus(`❌ Failed to Initialize WebSocket: ${error.message}`, 'error');
            disableButtons();
        }
    }

    function sendTestMessage() {
        if (!channel) {
            log('❌ No active channel to send message');
            return;
        }

        log('✅ Ready to receive messages on test-channel');
        log('ℹ️ Use "Trigger Server Message" to send a real message through Laravel');
    }

    function triggerServerMessage() {
        log('📤 Triggering server message via API...');

        const url = `/api/soketi/send-test/${CONFIG.APP_ID}`;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (!csrfToken) {
            log('❌ CSRF token not found');
            return;
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                channel: 'test-channel',
                event: 'server-message',
                data: {
                    message: 'Hello from server!',
                    timestamp: new Date().toISOString(),
                    source: 'test-page'
                }
            })
        })
            .then(response => {
                log(`📊 Server response status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    log('✅ Server message triggered successfully');
                } else {
                    log(`❌ Server message failed: ${data.message || 'Unknown error'}`);
                }
            })
            .catch(error => {
                log(`❌ Error triggering server message: ${error.message}`);
            });
    }

    function getChannelInfo() {
        log('📊 Getting channel information...');

        const url = `/api/soketi/channel-info/${CONFIG.APP_ID}?channel=test-channel`;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(url, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    log(`✅ Channel info: ${JSON.stringify(data.info, null, 2)}`);
                } else {
                    log(`❌ Failed to get channel info: ${data.message}`);
                }
            })
            .catch(error => {
                log(`❌ Error getting channel info: ${error.message}`);
            });
    }

    function testApiEndpoint() {
        log('🧪 Testing API endpoint...');

        const url = `/api/soketi/test/${CONFIG.APP_ID}`;

        fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(response => {
                log(`📊 API response status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                document.getElementById('api-response').textContent = JSON.stringify(data, null, 2);
                document.getElementById('api-results').classList.remove('hidden');
                document.getElementById('api-results').className = 'p-4 rounded-lg border border-green-200 bg-green-50';
                log('✅ API test endpoint successful');
            })
            .catch(error => {
                document.getElementById('api-response').textContent = error.message;
                document.getElementById('api-results').classList.remove('hidden');
                document.getElementById('api-results').className = 'p-4 rounded-lg border border-red-200 bg-red-50';
                log(`❌ API test failed: ${error.message}`);
            });
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        log('🚀 Page loaded, starting tests...');

        // Debug configuration first
        debugConfig();

        // Auto-start tests after a short delay
        setTimeout(() => {
            testServerConnection();
            setTimeout(() => {
                testWebSocketConnection();
            }, 2000);
        }, 1000);
    });
</script>
</body>
</html>
