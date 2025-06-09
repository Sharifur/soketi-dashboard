<?php

namespace App\Http\Controllers;

use App\Models\SoketiWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookTestController extends Controller
{
    public function test(SoketiWebhook $webhook)
    {
        return view('webhook.test', compact('webhook'));
    }

    public function sendTest(Request $request, SoketiWebhook $webhook)
    {
        try {
            // Prepare headers
            $headers = array_merge([
                'Content-Type' => 'application/json',
                'User-Agent' => 'Soketi-Dashboard/1.0',
            ], $webhook->headers ?? []);

            // Send the webhook
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($webhook->webhook_url, $webhook->payload ?? []);

            // Update webhook record
            if ($response->successful()) {
                $webhook->markAsSent($response->status(), $response->body());
                $status = 'success';
                $message = 'Webhook sent successfully!';
            } else {
                $webhook->markAsFailed($response->status(), $response->body());
                $status = 'error';
                $message = 'Webhook failed with status: ' . $response->status();
            }

            return response()->json([
                'status' => $status,
                'message' => $message,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
                'response_time' => $response->transferStats?->getTransferTime() * 1000 ?? 0,
            ]);

        } catch (\Exception $e) {
            $webhook->markAsFailed(0, $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error sending webhook: ' . $e->getMessage(),
                'response_status' => 0,
                'response_body' => $e->getMessage(),
                'response_time' => 0,
            ]);
        }
    }
}
