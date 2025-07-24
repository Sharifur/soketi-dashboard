<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Pusher\Pusher;

class SoketiQuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.soketi-quick-actions';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function sendTestMessage()
    {
        try {
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                [
                    'host' => env('PUSHER_HOST', '127.0.0.1'),
                    'port' => env('PUSHER_PORT', 6001),
                    'scheme' => env('PUSHER_SCHEME', 'http'),
                    'encrypted' => env('PUSHER_SCHEME', 'http') === 'https',
                    'useTLS' => env('PUSHER_SCHEME', 'http') === 'https',
                ]
            );

            $result = $pusher->trigger('test-channel', 'test-event', [
                'message' => 'Test message from admin dashboard',
                'timestamp' => now()->toISOString(),
                'sender' => 'Admin Panel'
            ]);

            Notification::make()
                ->title('Test message sent successfully!')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to send test message')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function checkSoketiStatus(): int
    {
        try {
            $response = Http::timeout(5)->get('http://127.0.0.1:6001');

            if ($response->successful()) {
                Notification::make()
                    ->title('Soketi is online')
                    ->body('Server is responding normally')
                    ->success()
                    ->send();

                return $response->status();
            } else {
                throw new \Exception('Server returned status: ' . $response->status());
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Soketi is offline')
                ->body('Cannot connect to Soketi server: ' . $e->getMessage())
                ->danger()
                ->send();
        }

        return 0; // Return 0 if Soketi is offline or unreachable
    }

    public function broadcastToAll(): void
    {
        try {
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                [
                    'host' => env('PUSHER_HOST', '127.0.0.1'),
                    'port' => env('PUSHER_PORT', 6001),
                    'scheme' => env('PUSHER_SCHEME', 'http'),
                    'encrypted' => env('PUSHER_SCHEME', 'http') === 'https',
                    'useTLS' => env('PUSHER_SCHEME', 'http') === 'https',
                ]
            );

            $result = $pusher->trigger('global-announcements', 'admin-message', [
                'title' => 'Admin Announcement',
                'message' => 'This is a broadcast message to all connected clients',
                'timestamp' => now()->toISOString(),
                'type' => 'info'
            ]);

            Notification::make()
                ->title('Broadcast sent successfully!')
                ->body('Message sent to all connected clients')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to send broadcast')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getViewData(): array
    {
        return [
            'metrics_url' => env('SOKETI_METRICS_URL', 'http://127.0.0.1:9601/metrics'),
            'websocket_url' => env('SOKETI_WEBSOCKET_URL', 'ws://127.0.0.1:6001'),
            'soketi_status' => $this->checkSoketiStatus(),
            'soketi_url' => config('broadcasting.connections.pusher.options.host', '')
        ];
    }
}
