<?php

namespace App\Filament\Resources\SoketiAppResource\Pages;

use App\Filament\Resources\SoketiAppResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSoketiApps extends ListRecords
{
    protected static string $resource = SoketiAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
