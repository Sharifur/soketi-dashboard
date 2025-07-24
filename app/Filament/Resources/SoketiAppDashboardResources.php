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

class SoketiAppDashboardResources extends Resource
{
    protected static ?string $model = SoketiApp::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Socket Apps';
    protected static ?string $navigationGroup = 'Socket Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('App ID')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('key')
                            ->label('App Key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('secret')
                            ->label('App Secret')
                            ->required()
                            ->password()
                            ->revealable()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('enabled')
                            ->label('Enabled')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Connection Limits')
                    ->schema([
                        Forms\Components\TextInput::make('max_connections')
                            ->label('Max Connections')
                            ->numeric()
                            ->default(1000)
                            ->minValue(1),

                        Forms\Components\TextInput::make('max_backend_events_per_second')
                            ->label('Max Backend Events Per Second')
                            ->numeric()
                            ->default(100)
                            ->minValue(1),

                        Forms\Components\TextInput::make('max_client_events_per_second')
                            ->label('Max Client Events Per Second')
                            ->numeric()
                            ->default(50)
                            ->minValue(1),

                        Forms\Components\TextInput::make('max_read_requests_per_second')
                            ->label('Max Read Requests Per Second')
                            ->numeric()
                            ->default(100)
                            ->minValue(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Feature Settings')
                    ->schema([
                        Forms\Components\Toggle::make('enable_client_messages')
                            ->label('Enable Client Messages')
                            ->default(true),

                        Forms\Components\Toggle::make('enable_user_authentication')
                            ->label('Enable User Authentication')
                            ->default(false),

                        Forms\Components\TextInput::make('max_presence_members_per_channel')
                            ->label('Max Presence Members Per Channel')
                            ->numeric()
                            ->default(100)
                            ->minValue(1),

                        Forms\Components\TextInput::make('max_presence_member_size_in_kb')
                            ->label('Max Presence Member Size (KB)')
                            ->numeric()
                            ->default(2)
                            ->minValue(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Message Limits')
                    ->schema([
                        Forms\Components\TextInput::make('max_channel_name_length')
                            ->label('Max Channel Name Length')
                            ->numeric()
                            ->default(100)
                            ->minValue(1),

                        Forms\Components\TextInput::make('max_event_channels_at_once')
                            ->label('Max Event Channels At Once')
                            ->numeric()
                            ->default(10)
                            ->minValue(1),

                        Forms\Components\TextInput::make('max_event_name_length')
                            ->label('Max Event Name Length')
                            ->numeric()
                            ->default(100)
                            ->minValue(1),

                        Forms\Components\TextInput::make('max_event_payload_in_kb')
                            ->label('Max Event Payload (KB)')
                            ->numeric()
                            ->default(100)
                            ->minValue(1),

                        Forms\Components\TextInput::make('max_event_batch_size')
                            ->label('Max Event Batch Size')
                            ->numeric()
                            ->default(10)
                            ->minValue(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Webhooks')
                    ->schema([
                        Forms\Components\Repeater::make('webhooks')
                            ->label('Webhook URLs')
                            ->schema([
                                Forms\Components\TextInput::make('url')
                                    ->label('Webhook URL')
                                    ->url()
                                    ->required(),

                                Forms\Components\TagsInput::make('event_types')
                                    ->label('Event Types')
                                    ->suggestions([
                                        'member_added',
                                        'member_removed',
                                        'channel_occupied',
                                        'channel_vacated',
                                    ]),

                                Forms\Components\TextInput::make('headers')
                                    ->label('Custom Headers (JSON)')
                                    ->placeholder('{"Authorization": "Bearer token"}')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->addActionLabel('Add Webhook')
                            ->defaultItems(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('App ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('key')
                    ->label('App Key')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(function (SoketiApp $record): string {
                        return $record->key;
                    }),

                Tables\Columns\ToggleColumn::make('enabled')
                    ->label('Status')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_connections')
                    ->label('Max Connections')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('enable_client_messages')
                    ->label('Client Messages')
                    ->boolean(),

                Tables\Columns\TextColumn::make('webhooks_count')
                    ->label('Webhooks')
                    ->counts('webhooks')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('enabled'),
                Tables\Filters\TernaryFilter::make('enable_client_messages'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('test_connection')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function (SoketiApp $record) {
                        // Add your connection test logic here
                        // For example, try to connect to the websocket
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('enable')
                        ->icon('heroicon-o-check')
                        ->action(fn (Collection $records) => $records->each->update(['enabled' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('disable')
                        ->icon('heroicon-o-x-mark')
                        ->action(fn (Collection $records) => $records->each->update(['enabled' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('App Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('App ID'),
                        Infolists\Components\TextEntry::make('key')
                            ->label('App Key')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('secret')
                            ->label('App Secret')
                            ->copyable()
                            ->placeholder('Hidden for security'),
                        Infolists\Components\IconEntry::make('enabled')
                            ->boolean(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Configuration')
                    ->schema([
                        Infolists\Components\TextEntry::make('max_connections')
                            ->label('Max Connections')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('max_backend_events_per_second')
                            ->label('Backend Events/sec'),
                        Infolists\Components\TextEntry::make('max_client_events_per_second')
                            ->label('Client Events/sec'),
                        Infolists\Components\IconEntry::make('enable_client_messages')
                            ->label('Client Messages')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('enable_user_authentication')
                            ->label('User Authentication')
                            ->boolean(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Limits')
                    ->schema([
                        Infolists\Components\TextEntry::make('max_presence_members_per_channel')
                            ->label('Presence Members/Channel'),
                        Infolists\Components\TextEntry::make('max_event_payload_in_kb')
                            ->label('Event Payload (KB)'),
                        Infolists\Components\TextEntry::make('max_channel_name_length')
                            ->label('Channel Name Length'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Webhooks')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('webhooks')
                            ->schema([
                                Infolists\Components\TextEntry::make('url')
                                    ->label('URL')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('event_types')
                                    ->label('Events')
                                    ->badge()
                                    ->separator(','),
                            ])
                            ->columns(2),
                    ])
                    ->collapsed(),
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
}
