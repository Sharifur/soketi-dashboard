<?php

namespace App\Filament\Resources\SoketiDebugResource\Pages;

use App\Filament\Resources\SoketiDebugResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSoketiDebugEvents extends ListRecords
{
    protected static string $resource = SoketiDebugResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Events')
                ->badge(static::getModel()::count()),

            'connections' => Tab::make('Connections')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event_type', 'connection'))
                ->badge(static::getModel()::where('event_type', 'connection')->count())
                ->badgeColor('success'),

            'subscriptions' => Tab::make('Subscriptions')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('event_type', ['subscription', 'unsubscription']))
                ->badge(static::getModel()::whereIn('event_type', ['subscription', 'unsubscription'])->count())
                ->badgeColor('info'),

            'messages' => Tab::make('Messages')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event_type', 'message'))
                ->badge(static::getModel()::where('event_type', 'message')->count())
                ->badgeColor('primary'),

            'today' => Tab::make('Today')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('timestamp', today()))
                ->badge(static::getModel()::whereDate('timestamp', today())->count()),
        ];
    }
}
