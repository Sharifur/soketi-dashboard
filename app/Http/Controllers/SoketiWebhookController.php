<?php

namespace App\Http\Controllers;

use App\Models\SoketiDebugEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SoketiWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Validate the webhook signature if needed
        // $this->validateWebhookSignature($request);

        $event = $request->input('event');
        $channel = $request->input('channel');
        $appId = $request->input('app_id');
        $socketId = $request->input('socket_id');
        $data = $request->input('data', []);
        $userId = $data['user_id'] ?? null;

        // Map Soketi events to our event types
        $eventType = match ($event) {
            'client_event' => 'message',
            'subscription' => 'subscription',
            'unsubscribe' => 'unsubscription',
            'disconnect' => 'disconnection',
            'connect' => 'connection',
            default => $event,
        };

        // Log the event
        SoketiDebugEvent::create([
            'app_id' => $appId,
            'event_type' => $eventType,
            'channel' => $channel,
            'event_name' => $data['event'] ?? null,
            'payload' => $data,
            'socket_id' => $socketId,
            'user_id' => $userId,
            'timestamp' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    protected function validateWebhookSignature(Request $request)
    {
        // Implement webhook signature validation if needed
    }
}
