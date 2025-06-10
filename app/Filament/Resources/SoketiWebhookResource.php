<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SoketiWebhookResource\Pages;
use App\Models\SoketiWebhook;
use App\Models\SoketiApp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class SoketiWebhookResource extends Resource
{
    protected static ?string $model = SoketiWebhook::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Webhooks';

    protected static ?string $modelLabel = 'Webhook';

    protected static ?string $pluralModelLabel = 'Webhooks';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Soketi Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Webhook Details')
                    ->description('Information about the webhook event')
                    ->schema([
                        Forms\Components\Select::make('app_id')
                            ->label('Soketi Application')
                            ->options(SoketiApp::pluck('name', 'app_id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('event_name')
                            ->label('Event Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., channel_occupied, member_added'),

                        Forms\Components\TextInput::make('webhook_url')
                            ->label('Webhook URL')
                            ->required()
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://your-app.com/webhooks/soketi'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'sent' => 'Sent',
                                'failed' => 'Failed',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Payload & Headers')
                    ->description('Webhook payload and HTTP headers')
                    ->schema([
                        Forms\Components\Textarea::make('payload_json')
                            ->label('Payload (JSON)')
                            ->rows(6)
                            ->placeholder('{"event": "channel_occupied", "channel": "private-user.123"}')
                            ->helperText('Enter the webhook payload as JSON')
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->payload) {
                                    $component->state(json_encode($record->payload, JSON_PRETTY_PRINT));
                                }
                            }),

                        Forms\Components\KeyValue::make('headers')
                            ->label('HTTP Headers')
                            ->keyLabel('Header Name')
                            ->valueLabel('Header Value')
                            ->reorderable()
                            ->addActionLabel('Add Header')
                            ->default([
                                'Content-Type' => 'application/json',
                                'User-Agent' => 'Soketi/1.0',
                            ]),
                    ])->columns(1),

                Forms\Components\Section::make('Delivery Information')
                    ->description('Webhook delivery status and timing')
                    ->schema([
                        Forms\Components\TextInput::make('attempts')
                            ->label('Attempts')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(10),

                        Forms\Components\TextInput::make('response_status')
                            ->label('Response Status Code')
                            ->numeric()
                            ->placeholder('200, 404, 500, etc.'),

                        Forms\Components\DateTimePicker::make('sent_at')
                            ->label('Sent At')
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('next_retry_at')
                            ->label('Next Retry At')
                            ->nullable(),

                        Forms\Components\Textarea::make('response_body')
                            ->label('Response Body')
                            ->rows(4)
                            ->placeholder('Server response content')
                            ->maxLength(2000),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('soketiApp.name')
                    ->label('Application')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->placeholder('No App'),

                Tables\Columns\TextColumn::make('event_name')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'channel_occupied' => 'success',
                        'channel_vacated' => 'warning',
                        'member_added' => 'info',
                        'member_removed' => 'gray',
                        'client_event' => 'purple',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('webhook_url')
                    ->label('URL')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->webhook_url ?? 'No URL')
                    ->copyable()
                    ->fontFamily('mono'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'sent',
                        'danger' => 'failed',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'sent',
                        'heroicon-o-x-circle' => 'failed',
                    ]),

                Tables\Columns\TextColumn::make('attempts')
                    ->label('Attempts')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null || $state === 0 => 'gray',
                        $state <= 2 => 'success',
                        $state <= 4 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('response_status')
                    ->label('Status Code')
                    ->alignCenter()
                    ->fontFamily('mono')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 200 && $state < 300 => 'success',
                        $state >= 300 && $state < 400 => 'info',
                        $state >= 400 && $state < 500 => 'warning',
                        $state >= 500 => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('--'),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Sent')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->placeholder('Not sent')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('next_retry_at')
                    ->label('Next Retry')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->placeholder('No retry')
                    ->visible(fn ($record) => $record && $record->status === 'failed')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id')
                    ->label('Application')
                    ->options(SoketiApp::pluck('app_name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                    ]),

                Tables\Filters\SelectFilter::make('event_name')
                    ->label('Event Type')
                    ->options([
                        'channel_occupied' => 'Channel Occupied',
                        'channel_vacated' => 'Channel Vacated',
                        'member_added' => 'Member Added',
                        'member_removed' => 'Member Removed',
                        'client_event' => 'Client Event',
                    ]),

                Tables\Filters\Filter::make('failed_webhooks')
                    ->label('Failed Webhooks')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'failed')),

                Tables\Filters\Filter::make('recent_webhooks')
                    ->label('Last 24 Hours')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDay())),

                Tables\Filters\Filter::make('high_attempts')
                    ->label('High Retry Count (3+)')
                    ->query(fn (Builder $query): Builder => $query->where('attempts', '>=', 3)),

                Tables\Filters\Filter::make('ready_for_retry')
                    ->label('Ready for Retry')
                    ->query(function (Builder $query): Builder {
                        return $query->where('status', 'failed')
                            ->where('attempts', '<', 5)
                            ->where(function ($q) {
                                $q->whereNull('next_retry_at')
                                    ->orWhere('next_retry_at', '<=', now());
                            });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (SoketiWebhook $record) =>
                        $record &&
                        $record->status === 'failed' &&
                        method_exists($record, 'shouldRetry') &&
                        $record->shouldRetry()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Retry Webhook Delivery')
                    ->modalDescription('This will attempt to deliver the webhook again.')
                    ->action(function (SoketiWebhook $record) {
                        // In a real implementation, you'd queue this for retry
                        $record->update([
                            'status' => 'pending',
                            'next_retry_at' => null,
                            'attempts' => ($record->attempts ?? 0) + 1,
                        ]);

                        return redirect()->back();
                    }),

                Tables\Actions\Action::make('mark_sent')
                    ->label('Mark as Sent')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (SoketiWebhook $record) => $record && $record->status !== 'sent')
                    ->requiresConfirmation()
                    ->action(function (SoketiWebhook $record) {
                        if (method_exists($record, 'markAsSent')) {
                            $record->markAsSent(200, 'Manually marked as sent');
                        } else {
                            $record->update([
                                'status' => 'sent',
                                'sent_at' => now(),
                                'response_status' => 200,
                                'response_body' => 'Manually marked as sent'
                            ]);
                        }
                        return redirect()->back();
                    }),

                Tables\Actions\Action::make('view_payload')
                    ->label('Payload')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->modalContent(fn (SoketiWebhook $record) => view('filament.modals.webhook-payload', [
                        'webhook' => $record
                    ]))
                    ->modalHeading('Webhook Payload')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Tables\Actions\Action::make('test_webhook')
                    ->label('Test')
                    ->icon('heroicon-o-beaker')
                    ->color('purple')
                    ->url(fn (SoketiWebhook $record) => route('webhook.test', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('retry_selected')
                        ->label('Retry Selected')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (SoketiWebhook $record) {
                                if ($record &&
                                    $record->status === 'failed' &&
                                    (!method_exists($record, 'shouldRetry') || $record->shouldRetry())) {
                                    $record->update([
                                        'status' => 'pending',
                                        'next_retry_at' => null,
                                        'attempts' => ($record->attempts ?? 0) + 1,
                                    ]);
                                }
                            });
                        }),

                    Tables\Actions\BulkAction::make('mark_sent_selected')
                        ->label('Mark as Sent')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (SoketiWebhook $record) {
                                if ($record && $record->status !== 'sent') {
                                    if (method_exists($record, 'markAsSent')) {
                                        $record->markAsSent(200, 'Bulk marked as sent');
                                    } else {
                                        $record->update([
                                            'status' => 'sent',
                                            'sent_at' => now(),
                                            'response_status' => 200,
                                            'response_body' => 'Bulk marked as sent'
                                        ]);
                                    }
                                }
                            });
                        }),

                    Tables\Actions\BulkAction::make('export_webhooks')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function ($records) {
                            // Implementation for exporting webhook data
                            return redirect()->back();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Webhook Overview')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('soketiApp.name')
                                    ->label('Application')
                                    ->badge()
                                    ->color('primary')
                                    ->placeholder('No Application'),

                                Infolists\Components\TextEntry::make('event_name')
                                    ->label('Event Type')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'channel_occupied' => 'success',
                                        'channel_vacated' => 'warning',
                                        'member_added' => 'info',
                                        'member_removed' => 'gray',
                                        'client_event' => 'purple',
                                        default => 'gray',
                                    }),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'sent' => 'success',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),
                    ]),

                Infolists\Components\Section::make('Webhook Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('webhook_url')
                            ->label('Webhook URL')
                            ->copyable()
                            ->fontFamily('mono')
                            ->placeholder('No URL'),

                        Infolists\Components\TextEntry::make('attempts')
                            ->label('Delivery Attempts')
                            ->badge()
                            ->color(fn (?int $state): string => match (true) {
                                $state === null || $state === 0 => 'gray',
                                $state <= 2 => 'success',
                                $state <= 4 => 'warning',
                                default => 'danger',
                            }),

                        Infolists\Components\TextEntry::make('response_status')
                            ->label('Last Response Status')
                            ->badge()
                            ->color(fn (?int $state): string => match (true) {
                                $state === null => 'gray',
                                $state >= 200 && $state < 300 => 'success',
                                $state >= 300 && $state < 400 => 'info',
                                $state >= 400 && $state < 500 => 'warning',
                                $state >= 500 => 'danger',
                                default => 'gray',
                            })
                            ->placeholder('No response yet'),
                    ])->columns(3),

                Infolists\Components\Section::make('HTTP Headers')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('headers')
                            ->label('Request Headers')
                            ->placeholder('No custom headers'),
                    ])
                    ->visible(fn (SoketiWebhook $record) => $record && !empty($record->headers)),

                Infolists\Components\Section::make('Webhook Payload')
                    ->schema([
                        Infolists\Components\TextEntry::make('payload_formatted')
                            ->label('JSON Payload')
                            ->getStateUsing(fn (SoketiWebhook $record) =>
                            $record && $record->payload
                                ? json_encode($record->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                                : 'No payload data'
                            )
                            ->fontFamily('mono')
                            ->placeholder('No payload data'),
                    ]),

                Infolists\Components\Section::make('Response Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('response_body')
                            ->label('Server Response')
                            ->fontFamily('mono')
                            ->placeholder('No response received')
                            ->limit(500),
                    ])
                    ->visible(fn (SoketiWebhook $record) => $record && !empty($record->response_body)),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime()
                            ->since(),

                        Infolists\Components\TextEntry::make('sent_at')
                            ->label('Sent At')
                            ->dateTime()
                            ->since()
                            ->placeholder('Not sent yet'),

                        Infolists\Components\TextEntry::make('next_retry_at')
                            ->label('Next Retry At')
                            ->dateTime()
                            ->since()
                            ->placeholder('No retry scheduled')
                            ->visible(fn (SoketiWebhook $record) => $record && $record->status === 'failed'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime()
                            ->since(),
                    ])->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSoketiWebhooks::route('/'),
            'create' => Pages\CreateSoketiWebhook::route('/create'),
            'view' => Pages\ViewSoketiWebhook::route('/{record}'),
            'edit' => Pages\EditSoketiWebhook::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $failedCount = static::getModel()::where('status', 'failed')->count();
            return $failedCount > 0 ? (string) $failedCount : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        try {
            $failedCount = static::getModel()::where('status', 'failed')->count();

            if ($failedCount > 10) {
                return 'danger';
            }

            if ($failedCount > 5) {
                return 'warning';
            }

            if ($failedCount > 0) {
                return 'warning';
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['soketiApp']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['event_name', 'webhook_url', 'soketiApp.name'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        if (!$record) {
            return [];
        }

        return [
            'Application' => $record->soketiApp?->name ?? 'No App',
            'Event' => $record->event_name ?? 'No Event',
            'Status' => $record->status ? ucfirst($record->status) : 'Unknown',
            'Attempts' => $record->attempts ?? 0,
        ];
    }
}
