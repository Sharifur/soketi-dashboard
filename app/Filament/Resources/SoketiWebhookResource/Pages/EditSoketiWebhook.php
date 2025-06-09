<?php

namespace App\Filament\Resources\SoketiWebhookResource\Pages;

use App\Filament\Resources\SoketiWebhookResource;
use Filament\Resources\Pages\EditRecord;

class EditSoketiWebhook extends EditRecord
{
    protected static string $resource = SoketiWebhookResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Convert JSON payload to array if provided
        if (isset($data['payload_json']) && !empty($data['payload_json'])) {
            try {
                $decoded = json_decode($data['payload_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \Filament\Notifications\Notification::make()
                        ->title('Invalid JSON')
                        ->body('Invalid JSON in payload field: ' . json_last_error_msg())
                        ->danger()
                        ->send();
                    return $data;
                }
                $data['payload'] = $decoded;
            } catch (\Exception $e) {
                \Filament\Notifications\Notification::make()
                    ->title('JSON Parse Error')
                    ->body('Error parsing JSON payload: ' . $e->getMessage())
                    ->danger()
                    ->send();
                return $data;
            }
            unset($data['payload_json']);
        }

        // Update the updated_at timestamp
        $data['updated_at'] = now();

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Convert payload array to JSON string for the form
        if (isset($data['payload']) && is_array($data['payload'])) {
            $data['payload_json'] = json_encode($data['payload'], JSON_PRETTY_PRINT);
        }

        return $data;
    }
}
