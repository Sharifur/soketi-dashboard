{{-- resources/views/filament/widgets/simple-app-stats.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Header with Copy Config Button --}}
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                App Statistics
            </h3>

            <button
                wire:click="openConfigModal"
                type="button"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors duration-200"
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

    {{-- Modal for showing config --}}
    @if($showConfigModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Background overlay --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeConfigModal"></div>

                {{-- Modal panel --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                    App Configuration
                                </h3>

                                {{-- Config content --}}
                                <div class="mt-2">
                                    <textarea
                                        readonly
                                        class="w-full h-64 p-3 border border-gray-300 rounded-md bg-gray-50 text-sm font-mono resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        id="config-textarea"
                                    >{{ $configData }}</textarea>
                                </div>

                                {{-- Copy instructions --}}
                                <p class="mt-2 text-sm text-gray-600">
                                    Select all text above (Ctrl+A) and copy (Ctrl+C) to copy the configuration.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Modal buttons --}}
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            onclick="copyConfigFromTextarea()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Copy to Clipboard
                        </button>
                        <button
                            type="button"
                            wire:click="closeConfigModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        function copyConfigFromTextarea() {
            const textarea = document.getElementById('config-textarea');
            if (!textarea) {
                alert('Config not found');
                return;
            }

            const configText = textarea.value;

            // Try modern clipboard API first
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(configText).then(() => {
                    alert('Configuration copied to clipboard!');
                    @this.call('closeConfigModal');
                }).catch(err => {
                    console.error('Modern clipboard failed:', err);
                    // Fall back to selection method
                    textarea.select();
                    textarea.setSelectionRange(0, 99999);
                    try {
                        document.execCommand('copy');
                        alert('Configuration copied to clipboard!');
                        @this.call('closeConfigModal');
                    } catch (e) {
                        alert('Please manually select and copy the text above.');
                    }
                });
            } else {
                // Fallback for older browsers
                textarea.select();
                textarea.setSelectionRange(0, 99999);
                try {
                    const successful = document.execCommand('copy');
                    if (successful) {
                        alert('Configuration copied to clipboard!');
                        @this.call('closeConfigModal');
                    } else {
                        alert('Please manually select and copy the text above.');
                    }
                } catch (err) {
                    alert('Please manually select and copy the text above.');
                }
            }
        }

        // Auto-select text when modal opens
        document.addEventListener('livewire:update', function() {
            const textarea = document.getElementById('config-textarea');
            if (textarea) {
                setTimeout(() => {
                    textarea.focus();
                    textarea.select();
                }, 100);
            }
        });
    </script>
</x-filament-widgets::widget>
