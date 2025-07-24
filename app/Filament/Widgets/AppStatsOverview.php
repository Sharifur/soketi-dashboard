<?php

namespace App\Filament\Widgets;

use App;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;

class AppStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Active Apps', App\Models\SoketiApp::where('status', 'active')->count())
                ->description('Currently active applications')
                ->descriptionIcon('heroicon-m-rocket-launch')
                ->color('info'),

            Stat::make('Total Apps', App\Models\SoketiApp::count())
                ->description('All applications')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('warning'),

            Stat::make('Connections Today', $this->getTodayConnections())
                ->description('Active connections today')
                ->descriptionIcon('heroicon-m-signal')
                ->color('primary'),
        ];
    }

    private function getTodayConnections(): int
    {
        // Implement your logic to get today's connections
        // This could be from your socket logs, database, or cache
        return rand(50, 200); // Replace with actual logic
    }
}
