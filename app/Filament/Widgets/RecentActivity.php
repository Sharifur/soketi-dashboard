<?php

namespace App\Filament\Widgets;

use App\Models\SoketiDebugEvent;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class RecentActivity extends ChartWidget
{
    protected static ?string $heading = 'Event Activity (Last 24 Hours)';
    protected static ?int $sort = 3;

    public ?string $filter = 'events';

    protected function getFilters(): ?array
    {
        return [
            'events' => 'Event Types',
            'timeline' => 'Events Timeline',
        ];
    }

    protected function getData(): array
    {
        if ($this->filter === 'timeline') {
            return $this->getTimelineData();
        }

        return $this->getEventTypeData();
    }

    protected function getEventTypeData(): array
    {
        // Get events by type for the last 24 hours
        $eventsByType = SoketiDebugEvent::where('timestamp', '>=', now()->subDay())
            ->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->pluck('count', 'event_type')
            ->toArray();

        $colors = [
            'connection' => 'rgb(34, 197, 94)',
            'disconnection' => 'rgb(239, 68, 68)',
            'subscription' => 'rgb(59, 130, 246)',
            'unsubscription' => 'rgb(249, 115, 22)',
            'message' => 'rgb(168, 85, 247)',
        ];

        $backgroundColors = [];
        $borderColors = [];
        $labels = [];
        $data = [];

        foreach ($eventsByType as $type => $count) {
            $labels[] = ucfirst($type);
            $data[] = $count;
            $color = $colors[$type] ?? 'rgb(107, 114, 128)';
            $backgroundColors[] = str_replace('rgb', 'rgba', str_replace(')', ', 0.8)', $color));
            $borderColors[] = $color;
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getTimelineData(): array
    {
        $hours = [];
        $events = [];

        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $hours[] = $hour->format('H:i');

            $count = SoketiDebugEvent::whereBetween('timestamp', [
                $hour,
                $hour->copy()->addHour()
            ])->count();

            $events[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Events per Hour',
                    'data' => $events,
                    'backgroundColor' => 'rgba(168, 85, 247, 0.1)',
                    'borderColor' => 'rgb(168, 85, 247)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $hours,
        ];
    }

    protected function getType(): string
    {
        return $this->filter === 'timeline' ? 'line' : 'doughnut';
    }

    protected function getOptions(): array
    {
        if ($this->filter === 'timeline') {
            return [
                'responsive' => true,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                    ],
                ],
                'plugins' => [
                    'legend' => [
                        'display' => false,
                    ],
                ],
            ];
        }

        return [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
