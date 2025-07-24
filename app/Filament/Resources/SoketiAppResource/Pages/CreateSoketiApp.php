<?php

namespace App\Filament\Resources\SoketiAppResource\Pages;

use App\Filament\Resources\SoketiAppResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateSoketiApp extends CreateRecord
{
    protected static string $resource = SoketiAppResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Soketi App Created Successfully')
            ->body("App '{$this->record->app_name}' has been created with ID: {$this->record->id}")
            ->success()
            ->persistent()
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->button()
                    ->url($this->getResource()::getUrl('view', ['record' => $this->record])),
                \Filament\Notifications\Actions\Action::make('test')
                    ->button()
                    ->url(route('soketi.test', $this->record))
                    ->openUrlInNewTab(),
            ])
            ->send();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure defaults are applied if not provided
        $defaults = config('soketi.defaults', []);

        return array_merge($defaults, array_filter($data, fn($value) => $value !== null));
    }
}
