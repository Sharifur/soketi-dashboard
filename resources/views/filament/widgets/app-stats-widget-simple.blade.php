{{-- resources/views/filament/widgets/debug-app-stats.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Debug Header --}}
        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h3 class="text-lg font-medium text-yellow-800 mb-2">Debug Mode - App Statistics</h3>
            <div class="space-x-2">
                <button
                    wire:click="testMethod"
                    class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                >
                    Test Livewire
                </button>
                <button
                    wire:click="copyAppConfig"
                    class="px-3 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                >
                    Copy Config (Livewire)
                </button>
                <button
                    onclick="testDirectCopy()"
                    class="px-3 py-2 bg-purple-500 text-white rounded hover:bg-purple-600"
                >
                    Test Direct Copy
                </button>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
            @foreach ($this->getCachedStats() as $stat)
                {{ $stat }}
            @endforeach
        </div>
    </x-filament::section>

    {{-- JavaScript for debugging and copying --}}
    <script>
        console.log('Debug widget script loaded');

        // Test direct copy function
        function testDirectCopy() {
            console.log('testDirectCopy called');
            const testText = 'This is a test copy text';
            copyToClipboard(testText);
        }

        // Handle copy config click from stat
        function handleCopyConfig(event) {
            console.log('handleCopyConfig called');
            event.preventDefault();
            event.stopPropagation();

            // Call Livewire method
            @this.call('copyAppConfig');
        }

        // Copy to clipboard function
        function copyToClipboard(text) {
            console.log('Attempting to copy:', text.substring(0, 100) + '...');

            if (navigator.clipboard && window.isSecureContext) {
                console.log('Using modern clipboard API');
                navigator.clipboard.writeText(text).then(() => {
                    console.log('Modern clipboard copy successful');
                    showNotification('Success!', 'Copied to clipboard using modern API', 'success');
                }).catch(err => {
                    console.error('Modern clipboard failed:', err);
                    fallbackCopy(text);
                });
            } else {
                console.log('Modern clipboard not available, using fallback');
                fallbackCopy(text);
            }
        }

        // Fallback copy method
        function fallbackCopy(text) {
            console.log('Using fallback copy method');
            try {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                textarea.style.left = '-999px';
                textarea.style.top = '-999px';
                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                textarea.setSelectionRange(0, 99999);

                const successful = document.execCommand('copy');
                document.body.removeChild(textarea);

                console.log('Fallback copy result:', successful);

                if (successful) {
                    showNotification('Success!', 'Copied to clipboard using fallback method', 'success');
                } else {
                    showNotification('Error', 'Failed to copy to clipboard', 'error');
                }
            } catch (err) {
                console.error('Fallback copy failed:', err);
                showNotification('Error', 'Copy failed: ' + err.message, 'error');
            }
        }

        // Simple notification function
        function showNotification(title, message, type) {
            console.log('Showing notification:', title, message, type);

            // Remove existing notifications
            const existing = document.querySelectorAll('.debug-notification');
            existing.forEach(el => el.remove());

            const notification = document.createElement('div');
            notification.className = `debug-notification fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <div>
                        <p class="font-medium">${title}</p>
                        <p class="text-sm">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">×</button>
                </div>
            `;
            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Listen for Livewire events
        document.addEventListener('livewire:initialized', () => {
            console.log('Livewire initialized');

            Livewire.on('trigger-copy-config', () => {
                console.log('trigger-copy-config event received');

                // Get config from session (you might need to pass this differently)
                @this.call('$refresh').then(() => {
                    // Try to get the config from a data attribute or make another call
                    const configData = @json(session('app_config_to_copy', ''));
                    if (configData) {
                        console.log('Got config data, copying...');
                        copyToClipboard(configData);
                    } else {
                        console.error('No config data found in session');
                        showNotification('Error', 'No configuration data found', 'error');
                    }
                });
            });
        });

        // Debug: Log when page loads
        console.log('Widget loaded. Record ID:', @json($this->record ?? 'null'));
    </script>
</x-filament-widgets::widget>
