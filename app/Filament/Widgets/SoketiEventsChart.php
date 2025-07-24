<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class SoketiEventsChart extends ChartWidget
{
    protected static ?string $heading = 'Events by Type';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        return Cache::remember('soketi_events_chart', 60, function () {
            // Simulate event data - in reality, you'd fetch from your metrics
            return [
                'datasets' => [
                    [
                        'data' => [45, 30, 15, 10],
                        'backgroundColor' => [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)',
                            'rgb(245, 101, 101)',
                            'rgb(249, 115, 22)',
                        ],
                    ],
                ],
                'labels' => ['Messages', 'Presence', 'Private', 'Client Events'],
            ];
        });
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '60s';
    }
}
