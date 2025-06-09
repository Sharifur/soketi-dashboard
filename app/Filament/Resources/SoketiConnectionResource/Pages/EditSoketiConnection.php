<?php

namespace App\Filament\Resources\SoketiConnectionResource\Pages;

use App\Filament\Resources\SoketiConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSoketiConnection extends EditRecord
{
    protected static string $resource = SoketiConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
