<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SoketiAppResource\Pages;
use App\Models\SoketiApp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SoketiAppResource extends Resource
{
    protected static ?string $model = SoketiApp::class;
    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationLabel = 'Applications';
    protected static ?string $modelLabel = 'Soketi Application';
    protected static ?string $pluralModelLabel = 'Soketi Applications';
    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'app_name',
            'app_description',
            'id',
            'key',
        ];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Name' => $record->app_name,
            'ID' => $record->id,
            'Status' => $record->enabled ? 'Enabled' : 'Disabled',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Application Information')
                    ->schema([
                        Forms\Components\TextInput::make('app_name')
                            ->label('Application Name')
                            ->required()
                            ->maxLength(255)
                            ->default('My App')
                            ->placeholder('My Awesome App'),

                        Forms\Components\Textarea::make('app_description')
                            ->label('Description')
                            ->nullable()
                            ->maxLength(1000)
                            ->placeholder('Enter application description'),
                    ])->columns(1),

                Forms\Components\Section::make('Basic Settings')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => Str::random(32)),

                        Forms\Components\TextInput::make('secret')
                            ->required()
                            ->password()
                            ->revealable()
                            ->default(fn () => Str::random(32)),

                        Forms\Components\Toggle::make('enabled')
                            ->required()
                            ->default(true),

                        Forms\Components\Toggle::make('enable_client_messages')
                            ->required()
                            ->default(true),

                        Forms\Components\Toggle::make('enable_user_authentication')
                            ->required()
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Limits & Rates')
                    ->schema([
                        Forms\Components\TextInput::make('max_connections')
                            ->numeric()
                            ->default(1000)
                            ->required(),

                        Forms\Components\TextInput::make('max_backend_events_per_sec')
                            ->numeric()
                            ->required()
                            ->default(100),

                        Forms\Components\TextInput::make('max_client_events_per_sec')
                            ->numeric()
                            ->required()
                            ->default(100),

                        Forms\Components\TextInput::make('max_read_req_per_sec')
                            ->numeric()
                            ->required()
                            ->default(100),
                    ])->columns(2),

                Forms\Components\Section::make('Channel Settings')
                    ->schema([
                        Forms\Components\TextInput::make('max_presence_members_per_channel')
                            ->numeric()
                            ->nullable(),

                        Forms\Components\TextInput::make('max_presence_member_size_in_kb')
                            ->numeric()
                            ->nullable(),

                        Forms\Components\TextInput::make('max_channel_name_length')
                            ->numeric()
                            ->nullable(),

                        Forms\Components\TextInput::make('max_event_channels_at_once')
                            ->numeric()
                            ->nullable(),

                        Forms\Components\TextInput::make('max_event_name_length')
                            ->numeric()
                            ->nullable(),

                        Forms\Components\TextInput::make('max_event_payload_in_kb')
                            ->numeric()
                            ->nullable(),

                        Forms\Components\TextInput::make('max_event_batch_size')
                            ->numeric()
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('Webhooks')
                    ->schema([
                        Forms\Components\Repeater::make('webhooks')
                            ->schema([
                                Forms\Components\Select::make('event')
                                    ->label('Event Type')
                                    ->options([
                                        'client_event' => 'Client Event',
                                        'channel_occupied' => 'Channel Occupied',
                                        'channel_vacated' => 'Channel Vacated',
                                        'member_added' => 'Member Added',
                                        'member_removed' => 'Member Removed',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('url')
                                    ->label('Webhook URL')
                                    ->url()
                                    ->required()
                                    ->placeholder('https://your-webhook-endpoint.com'),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Add Webhook')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                $state['event'] ?? null
                                    ? ucfirst(str_replace('_', ' ', $state['event'])) . ' → ' . $state['url']
                                    : null
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('app_name')
                    ->label('Application Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('id')
                    ->label('App ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('enabled')
                    ->boolean(),

                Tables\Columns\TextColumn::make('max_connections')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('enable_client_messages')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('enable_user_authentication')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('enabled'),
                Tables\Filters\TernaryFilter::make('enable_client_messages'),
                Tables\Filters\TernaryFilter::make('enable_user_authentication'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'edit' => Pages\EditSoketiApp::route('/{record}/edit'),
        ];
    }
}
