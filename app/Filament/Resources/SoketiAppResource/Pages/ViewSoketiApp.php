<?php

namespace App\Filament\Resources\SoketiAppResource\Pages;

use App\Filament\Resources\SoketiAppResource;
use App\Services\SoketiStatsService;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;
use Filament\Notifications\Notification;

class ViewSoketiApp extends ViewRecord
{
    protected static string $resource = SoketiAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('test_connection')
                ->label('Test Connection')
                ->icon('heroicon-o-signal')
                ->color('info')
                ->url(route('soketi.test', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('view_live_stats')
                ->label('Live Statistics')
                ->icon('heroicon-o-chart-bar')
                ->color('success')
                ->modalHeading('Live Statistics')
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->modalContent(function () {
                    $statsService = app(SoketiStatsService::class);
                    $appStats = $statsService->getAppStats($this->record);
                    $eventStats = $statsService->getEventStats($this->record);
                    $recentEvents = $statsService->getRecentEvents($this->record, 20);

                    return view('filament.components.live-stats', [
                        'app' => $this->record,
                        'appStats' => $appStats,
                        'eventStats' => $eventStats,
                        'recentEvents' => $recentEvents,
                    ]);
                }),

            Actions\Action::make('copy_config')
                ->label('Copy Config')
                ->icon('heroicon-o-clipboard-document')
                ->color('gray')
                ->action(function () {
                    $config = [
                        'app_id' => $this->record->id,
                        'key' => $this->record->key,
                        'secret' => $this->record->secret,
                        'cluster' => $this->record->cluster ?? 'mt1',
                        'host' => config('broadcasting.connections.pusher.options.host', '127.0.0.1'),
                        'port' => config('broadcasting.connections.pusher.options.port', 6001),
                        'scheme' => config('broadcasting.connections.pusher.options.scheme', 'http'),
                        'encrypted' => config('broadcasting.connections.pusher.options.encrypted', false),
                    ];

                    $configJson = json_encode($config, JSON_PRETTY_PRINT);

                    // Escape the JSON string for JavaScript
                    $escapedConfig = str_replace(['\\', "'", "\n", "\r"], ['\\\\', "\\'", '\\n', '\\r'], $configJson);

                    // Use the same working approach as the widget
                    $this->js("
                        const configText = `{$escapedConfig}`;
                        console.log('Attempting to copy config from header action');

                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(configText).then(() => {
                                console.log('Header action clipboard copy successful');
                                showSuccessNotification();
                            }).catch(err => {
                                console.error('Header action clipboard API failed:', err);
                                fallbackCopyMethod(configText);
                            });
                        } else {
                            console.log('Using fallback copy method in header action');
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
                                    console.log('Header action fallback copy successful');
                                    showSuccessNotification();
                                } else {
                                    console.error('Header action fallback copy failed');
                                    showErrorNotification();
                                }
                            } catch (err) {
                                console.error('Exception in header action fallback copy:', err);
                                showErrorNotification();
                            }
                        }

                        function showSuccessNotification() {
                            new FilamentNotification()
                                .title('Success!')
                                .body('Configuration copied to clipboard')
                                .success()
                                .send();
                        }

                        function showErrorNotification() {
                            new FilamentNotification()
                                .title('Copy Failed')
                                .body('Unable to copy to clipboard. Please try again.')
                                .danger()
                                .send();
                        }
                    ");
                }),

            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Application Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('app_name')
                                    ->label('Application Name')
                                    ->weight('bold'),
                                TextEntry::make('id')
                                    ->label('App ID')
                                    ->copyable(),
                                TextEntry::make('app_description')
                                    ->label('Description')
                                    ->columnSpanFull(),
                                TextEntry::make('enabled')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn($state) => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn($state) => $state ? 'Enabled' : 'Disabled'),
                            ]),
                    ]),

                Section::make('API Credentials')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('key')
                                    ->label('API Key')
                                    ->copyable(),
                                TextEntry::make('secret')
                                    ->label('API Secret')
                                    ->copyable()
                                    ->formatStateUsing(fn($state) => str_repeat('•', 20) . substr($state, -8)),
                            ]),
                    ]),

                Section::make('Connection Limits')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('max_connections')
                                    ->label('Max Connections'),
                                TextEntry::make('max_backend_events_per_sec')
                                    ->label('Backend Events/sec'),
                                TextEntry::make('max_client_events_per_sec')
                                    ->label('Client Events/sec'),
                            ]),
                    ]),

                Section::make('Current Statistics')
                    ->schema([
                        ViewEntry::make('current_stats')
                            ->view('filament.components.app-stats-summary')
                            ->viewData([
                                'app' => $this->record,
                            ]),
                    ]),

                Section::make('Recent Activity')
                    ->schema([
                        ViewEntry::make('recent_activity')
                            ->view('filament.components.recent-activity')
                            ->viewData([
                                'app' => $this->record,
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SoketiAppResource\Widgets\AppStatsWidget::class,
        ];
    }
}
