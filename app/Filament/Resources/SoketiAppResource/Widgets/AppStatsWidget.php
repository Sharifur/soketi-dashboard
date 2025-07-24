<?php

namespace App\Filament\Resources\SoketiAppResource\Widgets;

use App\Models\SoketiApp;
use App\Services\SoketiStatsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Notifications\Notification;

class AppStatsWidget extends BaseWidget
{
    public ?string $record = null;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $recordData = json_decode($this->record, true);
        $app = SoketiApp::find($recordData['id']);

        if (!$app) {
            return [];
        }

        $statsService = app(SoketiStatsService::class);
        $appStats = $statsService->getAppStats($app);
        $eventStats = $statsService->getEventStats($app);
        $channelStats = $statsService->getChannelStats($app);

        return [
            Stat::make('Current Connections', number_format($appStats['current_connections']))
                ->description("Max: " . number_format($appStats['max_connections']) . " | Utilization: {$appStats['utilization_percentage']}%")
                ->descriptionIcon('heroicon-m-link')
                ->chart($appStats['hourly_data']['data'] ?? [])
                ->color($this->getUtilizationColor($appStats['utilization_percentage'])),

            Stat::make('Active Channels', number_format($channelStats['active_channels']))
                ->description("Total: " . number_format($channelStats['total_channels']) . " channels")
                ->descriptionIcon('heroicon-m-chat-bubble-left-ellipsis')
                ->color('info'),

            Stat::make('Events (24h)', number_format($eventStats['events_last_24h']))
                ->description("Rate: " . number_format($eventStats['events_per_hour'], 1) . "/hour | Failed: " . number_format($eventStats['failed_events']))
                ->descriptionIcon('heroicon-m-bolt')
                ->color($eventStats['failed_events'] > 0 ? 'warning' : 'success'),

            Stat::make('Peak Connections', number_format($appStats['peak_connections_today']))
                ->description("Avg Duration: " . $this->formatDuration($appStats['avg_connection_duration']))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
        ];
    }

    protected function getUtilizationColor(float $utilization): string
    {
        if ($utilization >= 90) return 'danger';
        if ($utilization >= 75) return 'warning';
        if ($utilization >= 50) return 'info';
        return 'success';
    }

    protected function formatDuration(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    public function copyConfig(): void
    {
        $config = $this->getAppConfig();

        if (!$config) {
            Notification::make()
                ->title('Error')
                ->body('Unable to generate configuration')
                ->danger()
                ->send();
            return;
        }

        $configJson = json_encode($config, JSON_PRETTY_PRINT);
        $escapedConfig = str_replace(['\\', "'", "\n", "\r"], ['\\\\', "\\'", '\\n', '\\r'], $configJson);

        $this->js("
            const configText = `{$escapedConfig}`;
            console.log('Attempting to copy config');

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(configText).then(() => {
                    console.log('Clipboard copy successful');
                    \$wire.call('showCopySuccess');
                }).catch(err => {
                    console.error('Clipboard API failed:', err);
                    fallbackCopyMethod(configText);
                });
            } else {
                console.log('Using fallback copy method');
                fallbackCopyMethod(configText);
            }

            function fallbackCopyMethod(text) {
                try {
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    textarea.style.left = '-9999px';
                    document.body.appendChild(textarea);
                    textarea.focus();
                    textarea.select();
                    textarea.setSelectionRange(0, 99999);

                    const successful = document.execCommand('copy');
                    document.body.removeChild(textarea);

                    if (successful) {
                        console.log('Fallback copy successful');
                        \$wire.call('showCopySuccess');
                    } else {
                        console.error('Fallback copy failed');
                        \$wire.call('showCopyError');
                    }
                } catch (err) {
                    console.error('Exception in fallback copy:', err);
                    \$wire.call('showCopyError');
                }
            }
        ");
    }

    public function showCopySuccess(): void
    {
        Notification::make()
            ->title('Success!')
            ->body('Configuration copied to clipboard')
            ->success()
            ->duration(3000)
            ->send();
    }

    public function showCopyError(): void
    {
        Notification::make()
            ->title('Copy Failed')
            ->body('Unable to copy to clipboard. Please try again.')
            ->danger()
            ->duration(5000)
            ->send();
    }

    protected function getAppConfig(): ?array
    {
        if (!$this->record) {
            return null;
        }

        $recordData = json_decode($this->record, true);
        $app = SoketiApp::find($recordData['id']);

        if (!$app) {
            return null;
        }

        return [
            'app_id' => $app->id,
            'key' => $app->key,
            'secret' => $app->secret,
            'cluster' => $app->cluster ?? 'mt1',
            'host' => config('broadcasting.connections.pusher.options.host', '127.0.0.1'),
            'port' => config('broadcasting.connections.pusher.options.port', 6001),
            'scheme' => config('broadcasting.connections.pusher.options.scheme', 'ws'),
            'encrypted' => config('broadcasting.connections.pusher.options.encrypted', false),
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '15s'; // Refresh every 15 seconds for real-time feel
    }
}
