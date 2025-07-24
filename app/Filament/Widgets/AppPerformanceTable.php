<?php

// app/Filament/Widgets/AppPerformanceTable.php
namespace App\Filament\Widgets;

use App\Models\SoketiApp;
use App\Models\SoketiConnection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class AppPerformanceTable extends BaseWidget
{
    protected static ?string $heading = 'Application Performance';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SoketiApp::query()
                    ->withCount(['connections as active_connections' => function ($query) {
                        $query->where('is_connected', true);
                    }])
                    ->withCount(['connections as total_connections'])
                    ->withCount(['debugEvents as events_today' => function ($query) {
                        $query->whereDate('timestamp', today());
                    }])
                    ->withCount(['debugEvents as events_last_hour' => function ($query) {
                        $query->where('timestamp', '>=', now()->subHour());
                    }])
            )
            ->columns([
                Tables\Columns\TextColumn::make('app_name')
                    ->label('Application')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-signal')
                    ->iconColor(fn($record) => $record->enabled ? 'success' : 'danger'),

                Tables\Columns\BadgeColumn::make('enabled')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => $state ? 'Online' : 'Offline')
                    ->colors([
                        'success' => fn($state) => $state === true,
                        'danger' => fn($state) => $state === false,
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => fn($state) => $state === true,
                        'heroicon-o-x-circle' => fn($state) => $state === false,
                    ]),

                Tables\Columns\TextColumn::make('active_connections')
                    ->label('Active Connections')
                    ->formatStateUsing(fn($state, $record) => $state . ' / ' . number_format($record->max_connections))
                    ->sortable()
                    ->badge()
                    ->color(function ($state, $record) {
                        if ($record->max_connections == 0) return 'gray';
                        $utilization = ($state / $record->max_connections) * 100;
                        return $utilization > 80 ? 'danger' : ($utilization > 60 ? 'warning' : 'success');
                    }),

                Tables\Columns\TextColumn::make('utilization')
                    ->label('Utilization')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->max_connections == 0) return '0%';
                        $utilization = round(($record->active_connections / $record->max_connections) * 100, 1);
                        return $utilization . '%';
                    })
                    ->badge()
                    ->color(function ($state, $record) {
                        if ($record->max_connections == 0) return 'gray';
                        $utilization = ($record->active_connections / $record->max_connections) * 100;
                        return $utilization > 80 ? 'danger' : ($utilization > 60 ? 'warning' : 'success');
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw(
                            "CASE WHEN max_connections = 0 THEN 0 ELSE (
                                (SELECT COUNT(*) FROM soketi_connections WHERE app_id = soketi_apps.id AND is_connected = 1)
                                / max_connections * 100
                            ) END {$direction}"
                        );
                    }),

                Tables\Columns\TextColumn::make('events_today')
                    ->label('Events Today')
                    ->formatStateUsing(fn($state) => number_format($state))
                    ->sortable()
                    ->icon('heroicon-o-bolt')
                    ->color('info'),

                Tables\Columns\TextColumn::make('events_last_hour')
                    ->label('Events (1h)')
                    ->formatStateUsing(fn($state) => number_format($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_connections')
                    ->label('Total Connections')
                    ->formatStateUsing(fn($state) => number_format($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('response_time')
                    ->label('Response Time')
                    ->formatStateUsing(function ($state, $record) {
                        // You can implement a ping test here or cache the last known response time
                        return 'N/A'; // or implement actual response time testing
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('enabled')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Online only')
                    ->falseLabel('Offline only')
                    ->native(false),

                Tables\Filters\Filter::make('high_utilization')
                    ->label('High Utilization (>80%)')
                    ->query(fn (Builder $query): Builder =>
                    $query->whereRaw('
                            (SELECT COUNT(*) FROM soketi_connections
                             WHERE app_id = soketi_apps.id AND is_connected = 1) / max_connections * 100 > 80
                        ')
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('active_connections')
                    ->form([
                        Select::make('operator')
                            ->options([
                                '>' => 'Greater than',
                                '<' => 'Less than',
                                '=' => 'Equal to',
                            ])
                            ->default('>'),
                        TextInput::make('value')
                            ->numeric()
                            ->placeholder('Connection count'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (filled($data['value']) && filled($data['operator'])) {
                            return $query->having('active_connections', $data['operator'], $data['value']);
                        }
                        return $query;
                    }),

                Tables\Filters\Filter::make('events_filter')
                    ->form([
                        Select::make('period')
                            ->options([
                                'today' => 'Today',
                                'hour' => 'Last Hour',
                            ])
                            ->default('today'),
                        TextInput::make('min_events')
                            ->numeric()
                            ->placeholder('Minimum events'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (filled($data['min_events'])) {
                            $column = $data['period'] === 'hour' ? 'events_last_hour' : 'events_today';
                            return $query->having($column, '>=', $data['min_events']);
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view')
                        ->label('View Details')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn($record) => \App\Filament\Resources\SoketiAppResource::getUrl('view', ['record' => $record])),

                    Tables\Actions\Action::make('test')
                        ->label('Test Connection')
                        ->icon('heroicon-o-signal')
                        ->color('success')
                        ->url(fn($record) => route('soketi.test', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('edit')
                        ->label('Edit Settings')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->color('warning')
                        ->url(fn($record) => \App\Filament\Resources\SoketiAppResource::getUrl('edit', ['record' => $record])),

                    Tables\Actions\Action::make('toggle_status')
                        ->label(fn($record) => $record->enabled ? 'Disable' : 'Enable')
                        ->icon(fn($record) => $record->enabled ? 'heroicon-o-pause' : 'heroicon-o-play')
                        ->color(fn($record) => $record->enabled ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn($record) => ($record->enabled ? 'Disable' : 'Enable') . ' Application')
                        ->modalDescription(fn($record) =>
                            'Are you sure you want to ' . ($record->enabled ? 'disable' : 'enable') .
                            ' "' . $record->app_name . '"? This will ' .
                            ($record->enabled ? 'stop accepting new connections' : 'allow new connections') . '.'
                        )
                        ->action(function ($record) {
                            $record->update(['enabled' => !$record->enabled]);

                            \Filament\Notifications\Notification::make()
                                ->title($record->enabled ? 'App Enabled' : 'App Disabled')
                                ->body("'{$record->app_name}' has been " . ($record->enabled ? 'enabled' : 'disabled'))
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('disconnect_all')
                        ->label('Disconnect All')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn($record) => $record->active_connections > 0)
                        ->requiresConfirmation()
                        ->modalHeading('Disconnect All Connections')
                        ->modalDescription(fn($record) =>
                        "Are you sure you want to disconnect all {$record->active_connections} active connections for '{$record->app_name}'?"
                        )
                        ->action(function ($record) {
                            $disconnected = $record->connections()
                                ->where('is_connected', true)
                                ->update([
                                    'is_connected' => false,
                                    'disconnected_at' => now(),
                                ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Connections Disconnected')
                                ->body("Disconnected {$disconnected} connections from '{$record->app_name}'")
                                ->warning()
                                ->send();
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('enable_apps')
                        ->label('Enable Selected')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each->update(['enabled' => true]);

                            \Filament\Notifications\Notification::make()
                                ->title('Apps Enabled')
                                ->body("{$count} applications have been enabled")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('disable_apps')
                        ->label('Disable Selected')
                        ->icon('heroicon-o-pause')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Disable Applications')
                        ->modalDescription('Are you sure you want to disable the selected applications? This will prevent new connections.')
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each->update(['enabled' => false]);

                            \Filament\Notifications\Notification::make()
                                ->title('Apps Disabled')
                                ->body("{$count} applications have been disabled")
                                ->warning()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('disconnect_all_connections')
                        ->label('Disconnect All Connections')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Disconnect All Connections')
                        ->modalDescription('Are you sure you want to disconnect all active connections from the selected applications?')
                        ->action(function ($records) {
                            $totalDisconnected = 0;

                            foreach ($records as $record) {
                                $disconnected = $record->connections()
                                    ->where('is_connected', true)
                                    ->update([
                                        'is_connected' => false,
                                        'disconnected_at' => now(),
                                    ]);
                                $totalDisconnected += $disconnected;
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Connections Disconnected')
                                ->body("Disconnected {$totalDisconnected} connections from " . $records->count() . " applications")
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('refresh_stats')
                    ->label('Refresh')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(function () {
                        \Filament\Notifications\Notification::make()
                            ->title('Stats Refreshed')
                            ->body('Application performance data has been refreshed')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('create_app')
                    ->label('Create New App')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->url(\App\Filament\Resources\SoketiAppResource::getUrl('create')),
            ])
            ->defaultSort('active_connections', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->poll('30s') // Auto-refresh every 30 seconds
            ->emptyStateHeading('No Soketi Applications')
            ->emptyStateDescription('Create your first Soketi application to start monitoring WebSocket connections.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create App')
                    ->url(\App\Filament\Resources\SoketiAppResource::getUrl('create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }
}
