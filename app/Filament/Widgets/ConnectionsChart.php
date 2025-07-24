<?php
namespace App\Filament\Widgets;

use App\Models\SoketiConnection;
use App\Services\SoketiStatsService;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class ConnectionsChart extends ChartWidget
{
    protected static ?string $heading = 'Active Connections (Last 24 Hours)';
    protected static ?int $sort = 2;

    public ?string $filter = 'today';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last 7 days',
            'month' => 'Last 30 days',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter;

        // Determine the period based on filter
        $hours = match($filter) {
            'today' => 24,
            'week' => 168, // 7 * 24
            'month' => 720, // 30 * 24
            default => 24,
        };

        $interval = match($filter) {
            'today' => 1, // 1 hour intervals
            'week' => 6, // 6 hour intervals
            'month' => 24, // 24 hour intervals
            default => 1,
        };

        $labels = [];
        $connections = [];

        for ($i = $hours; $i >= 0; $i -= $interval) {
            $time = now()->subHours($i);

            $label = match($filter) {
                'today' => $time->format('H:i'),
                'week' => $time->format('D H:i'),
                'month' => $time->format('M j'),
                default => $time->format('H:i'),
            };

            $labels[] = $label;

            // Count active connections at each time point
            $count = SoketiConnection::where('connected_at', '<=', $time)
                ->where(function ($query) use ($time) {
                    $query->whereNull('disconnected_at')
                        ->orWhere('disconnected_at', '>', $time);
                })
                ->count();

            $connections[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Active Connections',
                    'data' => $connections,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Active Connections'
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Time'
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }
}
