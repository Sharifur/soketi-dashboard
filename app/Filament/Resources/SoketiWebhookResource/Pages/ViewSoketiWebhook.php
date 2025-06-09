<?php

namespace App\Filament\Resources\SoketiWebhookResource\Pages;

use App\Filament\Resources\SoketiWebhookResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSoketiWebhook extends ViewRecord
{
    protected static string $resource = SoketiWebhookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('retry')
                ->label('Retry Webhook')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'failed' && $this->record->shouldRetry())
                ->requiresConfirmation()
                ->modalHeading('Retry Webhook Delivery')
                ->modalDescription('This will attempt to deliver the webhook again.')
                ->action(function () {
                    $this->record->update([
                        'status' => 'pending',
                        'next_retry_at' => null,
                        'attempts' => $this->record->attempts + 1,
                    ]);

                    $this->refreshFormData([
                        'status',
                        'attempts',
                        'next_retry_at',
                    ]);

                    $this->notify('success', 'Webhook has been queued for retry.');
                }),

            Actions\Action::make('mark_sent')
                ->label('Mark as Sent')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status !== 'sent')
                ->requiresConfirmation()
                ->modalHeading('Mark Webhook as Sent')
                ->modalDescription('This will mark the webhook as successfully delivered.')
                ->action(function () {
                    $this->record->markAsSent(200, 'Manually marked as sent');

                    $this->refreshFormData([
                        'status',
                        'response_status',
                        'response_body',
                        'sent_at',
                    ]);

                    $this->notify('success', 'Webhook has been marked as sent.');
                }),

            Actions\Action::make('test_webhook')
                ->label('Test Webhook')
                ->icon('heroicon-o-beaker')
                ->color('purple')
                ->url(fn () => route('webhook.test', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('duplicate')
                ->label('Duplicate Webhook')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->action(function () {
                    $duplicate = $this->record->replicate();
                    $duplicate->status = 'pending';
                    $duplicate->attempts = 0;
                    $duplicate->response_status = null;
                    $duplicate->response_body = null;
                    $duplicate->sent_at = null;
                    $duplicate->next_retry_at = null;
                    $duplicate->save();

                    $this->notify('success', 'Webhook has been duplicated.');

                    return redirect()->to(static::getResource()::getUrl('view', ['record' => $duplicate]));
                }),
        ];
    }
}
