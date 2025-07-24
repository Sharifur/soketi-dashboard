<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class SoketiConnectionsChart extends ChartWidget
{
    protected static ?string $heading = 'Connections Over Time';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Cache::remember('soketi_connections_chart', 60, function () {
            // In a real implementation, you'd store historical data
            // For now, we'll simulate some data
            $hours = collect(range(0, 23))->map(function ($hour) {
                return [
                    'hour' => Carbon::today()->addHours($hour)->format('H:i'),
                    'connections' => rand(10, 100),
                ];
            });

            return [
                'datasets' => [
                    [
                        'label' => 'Active Connections',
                        'data' => $hours->pluck('connections')->toArray(),
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'fill' => true,
                    ],
                ],
                'labels' => $hours->pluck('hour')->toArray(),
            ];
        });

        return $data;
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '60s';
    }
}
