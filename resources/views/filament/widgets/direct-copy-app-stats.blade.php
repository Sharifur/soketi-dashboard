{{-- resources/views/filament/widgets/direct-copy-app-stats.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header with Copy Config Button --}}
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                App Statistics
            </h3>

            <button
                wire:click="copyConfig"
                type="button"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
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

    <script>
        console.log('working from direct copy app stats widget');

        document.addEventListener('livewire:initialized', () => {
            // Listen for the copy event from Livewire
            Livewire.on('copy-config-to-clipboard', (event) => {
                const configData = event.configData || event[0]?.configData;

                console.log('Copying config data:', configData);å

                if (!configData) {
                    console.error('No config data received');
                    @this.call('showCopyError');
                    return;
                }

                copyToClipboard(configData);
            });
        });

        function copyToClipboard(text) {
            // Try modern clipboard API first
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    @this.call('showCopySuccess');
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
                // Create a temporary textarea element
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                textarea.style.left = '-9999px';
                textarea.style.top = '-9999px';
                textarea.setAttribute('readonly', '');

                document.body.appendChild(textarea);
                textarea.focus();
                textarea.select();
                textarea.setSelectionRange(0, 99999); // For mobile devices

                const successful = document.execCommand('copy');
                document.body.removeChild(textarea);

                if (successful) {
                    @this.call('showCopySuccess');
                } else {
                    @this.call('showCopyError');
                }
            } catch (err) {
                console.error('Fallback copy failed:', err);
                @this.call('showCopyError');
            }
        }
    </script>
</x-filament-widgets::widget>
