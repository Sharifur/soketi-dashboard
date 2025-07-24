{{-- resources/views/filament/widgets/app-stats-widget.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header with Copy Config Button --}}
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                App Statistics
            </h3>

            <button
                type="button"
                wire:click="copyConfig"
                class="inline-flex items-center px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                Copy Config
            </button>
        </div>

        {{-- Stats Grid --}}
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($stats as $stat)
                {{ $stat }}
            @endforeach
        </div>
    </x-filament::section>

    {{-- JavaScript for clipboard functionality --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Listen for the copy event from Livewire
            Livewire.on('copy-to-clipboard', (event) => {
                const configText = event.config || event[0].config;
                copyToClipboard(configText);
            });
        });

        function copyToClipboard(text) {
            // Try modern clipboard API first
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showNotification('Success!', 'Configuration copied to clipboard', 'success');
                }).catch(err => {
                    console.error('Modern clipboard failed:', err);
                    fallbackCopy(text);
                });
            } else {
                fallbackCopy(text);
            }
        }

        function fallbackCopy(text) {
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
                textarea.setSelectionRange(0, 99999); // For mobile devices

                const successful = document.execCommand('copy');
                document.body.removeChild(textarea);

                if (successful) {
                    showNotification('Success!', 'Configuration copied to clipboard', 'success');
                } else {
                    showNotification('Error', 'Failed to copy configuration', 'error');
                }
            } catch (err) {
                console.error('Fallback copy failed:', err);
                showNotification('Error', 'Failed to copy configuration', 'error');
            }
        }

        function showNotification(title, message, type) {
            // Create a simple notification
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <div>
                        <p class="font-medium">${title}</p>
                        <p class="text-sm">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            `;
            document.body.appendChild(notification);

            // Auto remove after 3 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 3000);
        }
    </script>
</x-filament-widgets::widget>
