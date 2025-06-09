<?php

namespace App\Filament\Resources\SoketiConnectionResource\Pages;

use App\Filament\Resources\SoketiConnectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSoketiConnection extends CreateRecord
{
    protected static string $resource = SoketiConnectionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
