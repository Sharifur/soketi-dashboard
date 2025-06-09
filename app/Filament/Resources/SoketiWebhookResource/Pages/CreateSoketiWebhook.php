<?php

namespace App\Filament\Resources\SoketiWebhookResource\Pages;

use App\Filament\Resources\SoketiWebhookResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSoketiWebhook extends CreateRecord
{
    protected static string $resource = SoketiWebhookResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Convert JSON payload to array if provided
        if (isset($data['payload_json']) && !empty($data['payload_json'])) {
            try {
                $decoded = json_decode($data['payload_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Use Filament's notification system
                    \Filament\Notifications\Notification::make()
                        ->title('Invalid JSON')
                        ->body('Invalid JSON in payload field: ' . json_last_error_msg())
                        ->danger()
                        ->send();

                    // Don't proceed with creation if JSON is invalid
                    $this->halt();
                }
                $data['payload'] = $decoded;
            } catch (\Exception $e) {
                \Filament\Notifications\Notification::make()
                    ->title('JSON Parse Error')
                    ->body('Error parsing JSON payload: ' . $e->getMessage())
                    ->danger()
                    ->send();

                // Don't proceed with creation if there's an error
                $this->halt();
            }
            // Remove the JSON field as it's not part of the database
            unset($data['payload_json']);
        } else {
            // Set empty payload if no JSON provided
            $data['payload'] = [];
        }

        // Set default values if not provided
        if (!isset($data['attempts'])) {
            $data['attempts'] = 0;
        }

        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }

        // Ensure headers is an array
        if (!isset($data['headers']) || !is_array($data['headers'])) {
            $data['headers'] = [
                'Content-Type' => 'application/json',
                'User-Agent' => 'Soketi/1.0',
            ];
        }

        return $data;
    }
}
