<?php

namespace App\Filament\Widgets;

use App\Models\SoketiApp;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SoketiStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Apps', SoketiApp::count())
                ->description('Total number of Soketi applications')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->chart([7, 3, 4, 5, 6, 3, 5])
                ->color('primary'),

            Stat::make('Active Apps', SoketiApp::where('enabled', true)->count())
                ->description('Number of enabled applications')
                ->descriptionIcon('heroicon-m-signal')
                ->chart([7, 3, 4, 5, 6, 3, 5])
                ->color('success'),

            Stat::make('Inactive Apps', SoketiApp::where('enabled', false)->count())
                ->description('Number of disabled applications')
                ->descriptionIcon('heroicon-m-signal-slash')
                ->chart([7, 3, 4, 5, 6, 3, 5])
                ->color('danger'),
        ];
    }
}
