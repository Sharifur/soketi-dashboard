<?php

namespace App\Filament\Resources\SoketiAppResource\Pages;

use App\Filament\Resources\SoketiAppResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Cache;

class ListSoketiApps extends ListRecords
{
    protected static string $resource = SoketiAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('refresh_config')
                ->label('Refresh Config')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(function () {
                    Cache::forget('soketi_apps_config');
                    $this->redirect(request()->header('Referer'));
                }),
        ];
    }
}

