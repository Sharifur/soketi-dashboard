<?php

namespace App\Filament\Resources\SoketiWebhookResource\Pages;

use App\Filament\Resources\SoketiWebhookResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSoketiWebhooks extends ListRecords
{
    protected static string $resource = SoketiWebhookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('retry_failed')
                ->label('Retry All Failed')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Retry All Failed Webhooks')
                ->modalDescription('This will retry all failed webhooks that are eligible for retry.')
                ->action(function () {
                    $failedWebhooks = $this->getModel()::where('status', 'failed')
                        ->where('attempts', '<', 5)
                        ->where(function ($q) {
                            $q->whereNull('next_retry_at')
                                ->orWhere('next_retry_at', '<=', now());
                        })
                        ->get();

                    foreach ($failedWebhooks as $webhook) {
                        $webhook->update([
                            'status' => 'pending',
                            'next_retry_at' => null,
                            'attempts' => $webhook->attempts + 1,
                        ]);
                    }

                    $this->notify('success', "Queued {$failedWebhooks->count()} webhooks for retry.");
                }),

            Actions\Action::make('cleanup_old')
                ->label('Cleanup Old Webhooks')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cleanup Old Webhook Records')
                ->modalDescription('This will delete webhook records older than 30 days. This action cannot be undone.')
                ->action(function () {
                    $deleted = $this->getModel()::where('created_at', '<', now()->subDays(30))->delete();
                    $this->notify('success', "Deleted {$deleted} old webhook records.");
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Webhooks')
                ->badge($this->getModel()::count()),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge($this->getModel()::where('status', 'pending')->count())
                ->badgeColor('warning'),

            'sent' => Tab::make('Sent')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'sent'))
                ->badge($this->getModel()::where('status', 'sent')->count())
                ->badgeColor('success'),

            'failed' => Tab::make('Failed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'failed'))
                ->badge($this->getModel()::where('status', 'failed')->count())
                ->badgeColor('danger'),

            'today' => Tab::make('Today')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge($this->getModel()::whereDate('created_at', today())->count())
                ->badgeColor('primary'),

            'retry_ready' => Tab::make('Ready for Retry')
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->where('status', 'failed')
                        ->where('attempts', '<', 5)
                        ->where(function ($q) {
                            $q->whereNull('next_retry_at')
                                ->orWhere('next_retry_at', '<=', now());
                        });
                })
                ->badge($this->getModel()::where('status', 'failed')
                    ->where('attempts', '<', 5)
                    ->where(function ($q) {
                        $q->whereNull('next_retry_at')
                            ->orWhere('next_retry_at', '<=', now());
                    })->count())
                ->badgeColor('info'),

            'high_attempts' => Tab::make('High Attempts')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('attempts', '>=', 3))
                ->badge($this->getModel()::where('attempts', '>=', 3)->count())
                ->badgeColor('orange'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // You can add webhook statistics widgets here
        ];
    }
}
