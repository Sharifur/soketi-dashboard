<?php

namespace App\Filament\Resources\SoketiAppResource\Pages;

use App\Filament\Resources\SoketiAppResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSoketiApp extends EditRecord
{
    protected static string $resource = SoketiAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
