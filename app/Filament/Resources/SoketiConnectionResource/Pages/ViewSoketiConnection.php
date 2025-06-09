<?php

namespace App\Filament\Resources\SoketiConnectionResource\Pages;

use App\Filament\Resources\SoketiConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSoketiConnection extends ViewRecord
{
    protected static string $resource = SoketiConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('disconnect')
                ->label('Force Disconnect')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn () => $this->record->is_connected)
                ->requiresConfirmation()
                ->modalHeading('Force Disconnect Connection')
                ->modalDescription('This will forcefully disconnect the WebSocket connection. The user may reconnect automatically.')
                ->action(function () {
                    $this->record->update([
                        'is_connected' => false,
                        'disconnected_at' => now(),
                    ]);

                    $this->refreshFormData([
                        'is_connected',
                        'disconnected_at',
                    ]);

                    $this->notify('success', 'Connection has been disconnected.');
                }),
        ];
    }
}
