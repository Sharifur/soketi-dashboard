<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Active Connections Map
        </x-slot>

        <x-slot name="headerEnd">
            <div class="text-sm text-gray-500">
                Total: {{ $total_connections }} connections
            </div>
        </x-slot>

        <div class="space-y-6">
            <!-- Map Container -->
            <div class="relative">
                <div id="connections-map" class="w-full h-96 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-4xl mb-2">🗺️</div>
                        <p class="text-gray-600 font-medium">Interactive World Map</p>
                        <p class="text-sm text-gray-500">Showing {{ $total_connections }} active connections</p>
                    </div>
                </div>
            </div>

            <!-- Connection Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Countries List -->
                <div>
                    <h3 class="font-medium text-gray-900 mb-3">Connections by Country</h3>
                    <div class="space-y-2">
                        @foreach($countries as $country => $count)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <span class="font-medium text-gray-900">{{ $country }}</span>
                                <span class="text-sm font-semibold text-blue-600">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Cities List -->
                <div>
                    <h3 class="font-medium text-gray-900 mb-3">Active Cities</h3>
                    <div class="space-y-2">
                        @foreach($connections as $connection)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <div>
                                    <span class="font-medium text-gray-900">{{ $connection['city'] }}</span>
                                    <span class="text-sm text-gray-500 ml-1">({{ $connection['country'] }})</span>
                                </div>
                                <span class="text-sm font-semibold text-green-600">{{ $connection['connections'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-200">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ count($countries) }}</div>
                    <div class="text-sm text-gray-600">Countries</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ count($connections) }}</div>
                    <div class="text-sm text-gray-600">Cities</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $total_connections }}</div>
                    <div class="text-sm text-gray-600">Total Connections</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ round($total_connections / max(count($connections), 1), 1) }}</div>
                    <div class="text-sm text-gray-600">Avg per City</div>
                </div>
            </div>
        </div>
    </x-filament::section>

    @push('scripts')
        <script>
            // You can add interactive map functionality here using libraries like:
            // - Leaflet
            // - Google Maps
            // - Mapbox
            // For now, this is a placeholder
            console.log('Connections data:', @json($connections));
        </script>
    @endpush
</x-filament-widgets::widget>
