<?php

namespace App\Filament\Resources\SoketiConnectionResource\Pages;

use App\Filament\Resources\SoketiConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSoketiConnections extends ListRecords
{
    protected static string $resource = SoketiConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Connections')
                ->badge($this->getModel()::count()),

            'connected' => Tab::make('Connected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_connected', true))
                ->badge($this->getModel()::where('is_connected', true)->count())
                ->badgeColor('success'),

            'disconnected' => Tab::make('Disconnected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_connected', false))
                ->badge($this->getModel()::where('is_connected', false)->count())
                ->badgeColor('gray'),

            'today' => Tab::make('Today')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('connected_at', today()))
                ->badge($this->getModel()::whereDate('connected_at', today())->count())
                ->badgeColor('primary'),

            'presence' => Tab::make('Presence Channels')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('channel_name', 'like', 'presence-%'))
                ->badge($this->getModel()::where('channel_name', 'like', 'presence-%')->count())
                ->badgeColor('info'),

            'private' => Tab::make('Private Channels')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('channel_name', 'like', 'private-%'))
                ->badge($this->getModel()::where('channel_name', 'like', 'private-%')->count())
                ->badgeColor('warning'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // You can add widgets here to show connection statistics
        ];
    }
}
