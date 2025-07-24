<?php


use App\Models\SoketiApp;
use App\Services\SoketiStatsService;
use Filament\Widgets\Widget;

class SoketiQuickActions extends Widget
{
    protected static string $view = 'filament.widgets.soketi-quick-actions';
    protected static ?int $sort = 7;

    protected function getViewData(): array
    {
        $statsService = app(SoketiStatsService::class);

        return [
            'totalApps' => SoketiApp::count(),
            'enabledApps' => SoketiApp::where('enabled', true)->count(),
            'serverOnline' => $statsService->testConnection()['success'],
            'soketiStatus' => $statsService->testConnection(),
            'soketiUrl' => config('broadcasting.connections.pusher.options.host', '')
        ];
    }
}
