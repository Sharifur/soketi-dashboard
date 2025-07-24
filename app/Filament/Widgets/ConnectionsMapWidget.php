<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Http;

class ConnectionsMapWidget extends Widget
{
    protected static string $view = 'filament.widgets.connections-map';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function getViewData(): array
    {
        return [
            'connections' => $this->getConnectionsData(),
            'totalConnections' => $this->getTotalConnections(),
            'total_connections' => $this->getTotalConnections(),
            'countries' => $this->getConnectionsByCountry(),
        ];
    }

    private function getConnectionsData(): array
    {
        try {
            // This would typically come from your Soketi metrics or database
            // For now, we'll return mock data
            return [
                ['country' => 'Bangladesh', 'city' => 'Dhaka', 'connections' => 15, 'lat' => 23.8103, 'lng' => 90.4125],
                ['country' => 'United States', 'city' => 'New York', 'connections' => 8, 'lat' => 40.7128, 'lng' => -74.0060],
                ['country' => 'United Kingdom', 'city' => 'London', 'connections' => 5, 'lat' => 51.5074, 'lng' => -0.1278],
                ['country' => 'India', 'city' => 'Mumbai', 'connections' => 12, 'lat' => 19.0760, 'lng' => 72.8777],
                ['country' => 'Germany', 'city' => 'Berlin', 'connections' => 3, 'lat' => 52.5200, 'lng' => 13.4050],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getTotalConnections(): int
    {
        $connections = $this->getConnectionsData();
        return array_sum(array_column($connections, 'connections'));
    }

    private function getConnectionsByCountry(): array
    {
        $connections = $this->getConnectionsData();
        $countries = [];

        foreach ($connections as $connection) {
            $country = $connection['country'];
            if (!isset($countries[$country])) {
                $countries[$country] = 0;
            }
            $countries[$country] += $connection['connections'];
        }

        arsort($countries);
        return $countries;
    }
}
