<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SoketiDebugResource\Pages;
use App\Models\SoketiDebugEvent;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SoketiDebugResource extends Resource
{
    protected static ?string $model = SoketiDebugEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';

    protected static ?string $navigationLabel = 'Debug Events';

    protected static ?string $modelLabel = 'Debug Event';

    protected static ?string $pluralModelLabel = 'Debug Events';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationGroup = 'Soketi Management';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('timestamp')
                    ->label('Time')
                    ->dateTime()
                    ->sortable()
                    ->since(),

                Tables\Columns\TextColumn::make('event_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'connection' => 'success',
                        'subscription' => 'info',
                        'unsubscription' => 'warning',
                        'message' => 'primary',
                        'disconnection' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('soketiApp.app_name')
                    ->label('Application')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('channel')
                    ->label('Channel')
                    ->searchable()
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        str_starts_with($state ?? '', 'private-') => 'warning',
                        str_starts_with($state ?? '', 'presence-') => 'success',
                        default => 'info',
                    }),

                Tables\Columns\TextColumn::make('event_name')
                    ->label('Event')
                    ->searchable(),

                Tables\Columns\TextColumn::make('socket_id')
                    ->label('Socket')
                    ->toggleable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user_id')
                    ->label('User')
                    ->toggleable()
                    ->searchable(),
            ])
            ->defaultSort('timestamp', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->options([
                        'connection' => 'Connection',
                        'subscription' => 'Channel Subscription',
                        'unsubscription' => 'Channel Unsubscription',
                        'message' => 'Message',
                        'disconnection' => 'Disconnection',
                    ]),

                Tables\Filters\SelectFilter::make('app_id')
                    ->relationship('soketiApp', 'app_name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('channel_type')
                    ->form([
                        Forms\Components\Select::make('type')
                            ->options([
                                'private' => 'Private Channels',
                                'presence' => 'Presence Channels',
                                'public' => 'Public Channels',
                            ])
                            ->placeholder('All channel types'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['type'],
                            fn (Builder $query, $type): Builder => $query->where('channel', 'like', $type . '-%')
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn (SoketiDebugEvent $record): string => view('filament.modals.soketi-debug-payload-modal', [
                        'record' => $record,
                    ])->render()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->poll('5s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSoketiDebugEvents::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('created_at', today())->count();
    }
}
