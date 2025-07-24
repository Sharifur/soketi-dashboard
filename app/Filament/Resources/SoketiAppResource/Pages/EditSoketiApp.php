<?php

namespace App\Filament\Resources\SoketiAppResource\Pages;

use App\Filament\Resources\SoketiAppResource;
use App\Services\SoketiStatsService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditSoketiApp extends EditRecord
{
    protected static string $resource = SoketiAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('test_connection')
                ->label('Test Connection')
                ->icon('heroicon-o-signal')
                ->color('info')
                ->url(route('soketi.test', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('regenerate_credentials')
                ->label('Regenerate Credentials')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Regenerate API Credentials')
                ->modalDescription('This will generate new API key and secret. You will need to update your application configuration.')
                ->modalSubmitActionLabel('Regenerate')
                ->action(function () {
                    $this->record->regenerateCredentials();

                    Notification::make()
                        ->title('Credentials Regenerated')
                        ->body('New API key and secret have been generated.')
                        ->warning()
                        ->persistent()
                        ->send();

                    $this->fillForm();
                }),

            Actions\Action::make('view_stats')
                ->label('View Statistics')
                ->icon('heroicon-o-chart-bar')
                ->color('success')
                ->url($this->getResource()::getUrl('view', ['record' => $this->record])),

            Actions\Action::make('toggle_status')
                ->label(fn() => $this->record->enabled ? 'Disable App' : 'Enable App')
                ->icon(fn() => $this->record->enabled ? 'heroicon-o-pause' : 'heroicon-o-play')
                ->color(fn() => $this->record->enabled ? 'danger' : 'success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['enabled' => !$this->record->enabled]);

                    $status = $this->record->enabled ? 'enabled' : 'disabled';

                    Notification::make()
                        ->title("App {$status}")
                        ->body("The application has been {$status} successfully.")
                        ->success()
                        ->send();

                    $this->fillForm();
                }),

            Actions\DeleteAction::make()
                ->modalHeading('Delete Soketi App')
                ->modalDescription('Are you sure you want to delete this Soketi app? All associated connections and events will also be deleted.')
                ->successNotificationTitle('App deleted successfully'),

            Actions\ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('App Updated Successfully')
            ->body("Settings for '{$this->record->app_name}' have been saved.")
            ->success()
            ->send();
    }
}

