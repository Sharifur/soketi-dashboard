<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SoketiAppResource\Pages;
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

class SoketiAppResource extends Resource
{
    protected static ?string $model = SoketiApp::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Applications';

    protected static ?string $modelLabel = 'Soketi Application';

    protected static ?string $pluralModelLabel = 'Soketi Applications';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Application Details')
                    ->description('Basic information about your Soketi application')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->default('My App')
                            ->placeholder('My Awesome App'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Brief description of what this application does...'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive applications cannot accept new connections'),
                    ])->columns(1),

                Forms\Components\Section::make('Connection Settings')
                    ->description('Configure connection limits and behavior')
                    ->schema([
                        Forms\Components\TextInput::make('max_connections')
                            ->numeric()
                            ->default(500)
                            ->minValue(0)
                            ->helperText('Set to 0 for unlimited connections')
                            ->suffixIcon('heroicon-m-users'),

                        Forms\Components\Toggle::make('enable_client_messages')
                            ->label('Enable Client Messages')
                            ->default(true)
                            ->helperText('Allow clients to send messages to each other'),

                        Forms\Components\Toggle::make('enable_statistics')
                            ->label('Enable Statistics')
                            ->default(true)
                            ->helperText('Collect usage statistics for this application'),
                    ])->columns(3),

                Forms\Components\Section::make('Application Credentials')
                    ->description('These credentials are used by your client applications')
                    ->schema([
                        Forms\Components\TextInput::make('app_id')
                            ->label('Application ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(random_int(1000000000,9999999999))
                            ->disabled(fn (?SoketiApp $record) => $record !== null)
                            ->dehydrated()
                            ->helperText('Cannot be changed after creation'),

                        Forms\Components\TextInput::make('app_key')
                            ->label('Application Key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(Str::random(16))
                            ->disabled(fn (?SoketiApp $record) => $record !== null)
                            ->dehydrated()
                            ->helperText('Public key used by client applications'),

                        Forms\Components\TextInput::make('app_secret')
                            ->label('Application Secret')
                            ->required()
                            ->password()
                            ->default(Str::random(16))
                            ->revealable()
                            ->helperText('Keep this secret! Used for server-side authentication'),
                    ])->columns(1),

                Forms\Components\Section::make('Webhook Configuration')
                    ->description('Configure webhooks to receive real-time notifications')
                    ->schema([
                        Forms\Components\Toggle::make('enable_webhooks')
                            ->label('Enable Webhooks')
                            ->reactive()
                            ->helperText('Send HTTP requests when events occur'),

                        Forms\Components\Repeater::make('webhook_urls')
                            ->label('Webhook URLs')
                            ->visible(fn (Forms\Get $get) => $get('enable_webhooks'))
                            ->schema([
                                Forms\Components\TextInput::make('url')
                                    ->label('URL')
                                    ->url()
                                    ->required()
                                    ->placeholder('https://your-app.com/webhooks/soketi'),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add Webhook URL'),

                        Forms\Components\KeyValue::make('webhook_headers')
                            ->label('Webhook Headers')
                            ->visible(fn (Forms\Get $get) => $get('enable_webhooks'))
                            ->keyLabel('Header Name')
                            ->valueLabel('Header Value')
                            ->helperText('Custom headers to include with webhook requests'),

                        Forms\Components\CheckboxList::make('webhook_events')
                            ->label('Webhook Events')
                            ->visible(fn (Forms\Get $get) => $get('enable_webhooks'))
                            ->options([
                                'channel_occupied' => 'Channel Occupied',
                                'channel_vacated' => 'Channel Vacated',
                                'client_event' => 'Client Event',
                                'member_added' => 'Member Added',
                                'member_removed' => 'Member Removed',
                            ])
                            ->descriptions([
                                'channel_occupied' => 'When the first user joins a channel',
                                'channel_vacated' => 'When the last user leaves a channel',
                                'client_event' => 'When a client sends an event',
                                'member_added' => 'When a user joins a presence channel',
                                'member_removed' => 'When a user leaves a presence channel',
                            ])
                            ->columns(2),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('app_id')
                    ->label('App ID')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('App ID copied!')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('app_key')
                    ->label('App Key')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('App Key copied!')
                    ->fontFamily('mono')
                    ->limit(20),

                Tables\Columns\TextColumn::make('connections_count')
                    ->label('Connections')
                    ->getStateUsing(fn (SoketiApp $record) => $record->getCurrentConnectionCount())
                    ->badge()
                    ->color(fn (int $state, SoketiApp $record) =>
                    $state >= $record->max_connections ? 'danger' : 'success'
                    ),

                Tables\Columns\TextColumn::make('max_connections')
                    ->label('Max')
                    ->formatStateUsing(fn (int $state) => $state === 0 ? '∞' : $state),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All applications')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\Filter::make('has_webhooks')
                    ->label('Has Webhooks')
                    ->query(fn (Builder $query) => $query->where('enable_webhooks', true)),

                Tables\Filters\Filter::make('high_usage')
                    ->label('High Connection Usage')
                    ->query(fn (Builder $query) => $query->whereRaw('
                        (SELECT COUNT(*) FROM soketi_connections
                         WHERE soketi_connections.app_id = soketi_apps.app_id
                         AND is_connected = true) >= (max_connections * 0.8)
                    ')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('copy_credentials')
                    ->label('Copy Config')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('gray')
                    ->action(function (SoketiApp $record) {
                        // This would copy to clipboard in real implementation
                        return redirect()->back();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Application Configuration')
                    ->modalDescription(fn (SoketiApp $record) => new HtmlString("
                        <div class='space-y-2 font-mono text-sm'>
                            <div><strong>App ID:</strong> {$record->app_id}</div>
                            <div><strong>App Key:</strong> {$record->app_key}</div>
                            <div><strong>App Secret:</strong> " . str_repeat('*', strlen($record->app_secret)) . "</div>
                        </div>
                    ")),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Application Overview')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->size('lg')
                                    ->weight('bold'),
                                Infolists\Components\IconEntry::make('is_active')
                                    ->label('Status')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                            ]),
                        Infolists\Components\TextEntry::make('description')
                            ->placeholder('No description provided'),
                    ]),

                Infolists\Components\Section::make('Credentials')
                    ->schema([
                        Infolists\Components\TextEntry::make('app_id')
                            ->label('Application ID')
                            ->copyable()
                            ->fontFamily('mono'),
                        Infolists\Components\TextEntry::make('app_key')
                            ->label('Application Key')
                            ->copyable()
                            ->fontFamily('mono'),
                        Infolists\Components\TextEntry::make('app_secret')
                            ->label('Application Secret')
                            ->copyable()
                            ->fontFamily('mono')
                            ->formatStateUsing(fn () => str_repeat('*', 20) . ' (click to copy)'),
                    ])->columns(3),

                Infolists\Components\Section::make('Connection Settings')
                    ->schema([
                        Infolists\Components\TextEntry::make('max_connections')
                            ->label('Max Connections')
                            ->formatStateUsing(fn (int $state) => $state === 0 ? 'Unlimited' : number_format($state)),
                        Infolists\Components\IconEntry::make('enable_client_messages')
                            ->label('Client Messages')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('enable_statistics')
                            ->label('Statistics')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('current_connections')
                            ->label('Current Connections')
                            ->getStateUsing(fn (SoketiApp $record) => number_format($record->getCurrentConnectionCount()))
                            ->badge()
                            ->color('primary'),
                    ])->columns(4),

                Infolists\Components\Section::make('Webhook Configuration')
                    ->schema([
                        Infolists\Components\IconEntry::make('enable_webhooks')
                            ->label('Webhooks Enabled')
                            ->boolean(),
                        Infolists\Components\RepeatableEntry::make('webhook_urls')
                            ->label('Webhook URLs')
                            ->schema([
                                Infolists\Components\TextEntry::make('url')
                                    ->hiddenLabel(),
                            ])
                            ->visible(fn (SoketiApp $record) => $record->enable_webhooks),
                        Infolists\Components\KeyValueEntry::make('webhook_headers')
                            ->label('Custom Headers')
                            ->visible(fn (SoketiApp $record) => $record->enable_webhooks && !empty($record->webhook_headers)),
                    ])
                    ->visible(fn (SoketiApp $record) => $record->enable_webhooks),

                Infolists\Components\Section::make('Timestamps')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
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
            'index' => Pages\ListSoketiApps::route('/'),
            'create' => Pages\CreateSoketiApp::route('/create'),
            'view' => Pages\ViewSoketiApp::route('/{record}'),
            'edit' => Pages\EditSoketiApp::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getModel()::count();

        if ($count > 10) {
            return 'success';
        }

        if ($count > 5) {
            return 'warning';
        }

        return 'primary';
    }

    public static function afterCreate()
    {
        return function (SoketiApp $record) {
            $record->refresh(); // ensure latest data
            $record->fireModelEvent('created', false);
        };
    }

    public static function afterUpdate()
    {
        return function (SoketiApp $record) {
            $record->fireModelEvent('updated', false);
        };
    }

    public static function afterDelete()
    {
        return function (SoketiApp $record) {
            $record->fireModelEvent('deleted', false);
        };
    }

}
