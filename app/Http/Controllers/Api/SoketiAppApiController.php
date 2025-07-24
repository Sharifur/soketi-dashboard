<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SoketiApp;
use Illuminate\Http\Request;

class SoketiAppApiController extends Controller
{
    /**
     * Get all Soketi apps for the Soketi server
     * This endpoint is called by Soketi server to fetch app configurations
     */
    public function index(Request $request)
    {
        // Optional: Add authentication/authorization
        $token = $request->header('Authorization') ?? $request->get('token');

        if ($token !== config('soketi.api_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $apps = SoketiApp::where('enabled', true)->get();

        $formattedApps = $apps->map(function ($app) {
            return [
                'id' => $app->id,
                'key' => $app->key,
                'secret' => $app->secret,
                'maxConnections' => $app->max_connections,
                'enableClientMessages' => $app->enable_client_messages,
                'enabled' => $app->enabled,
                'maxBackendEventsPerSecond' => $app->max_backend_events_per_sec,
                'maxClientEventsPerSecond' => $app->max_client_events_per_sec,
                'maxReadRequestsPerSecond' => $app->max_read_req_per_sec,
                'webhooks' => $app->webhooks,
                'maxPresenceMembersPerChannel' => $app->max_presence_members_per_channel,
                'maxPresenceMemberSizeInKb' => $app->max_presence_member_size_in_kb,
                'maxChannelNameLength' => $app->max_channel_name_length,
                'maxEventChannelsAtOnce' => $app->max_event_channels_at_once,
                'maxEventNameLength' => $app->max_event_name_length,
                'maxEventPayloadInKb' => $app->max_event_payload_in_kb,
                'maxEventBatchSize' => $app->max_event_batch_size,
                'enableUserAuthentication' => $app->enable_user_authentication,
            ];
        });

        return response()->json([
            'apps' => $formattedApps
        ]);
    }

    /**
     * Get a specific app by ID
     */
    public function show(Request $request, string $appId)
    {
        // Optional: Add authentication/authorization
        $token = $request->header('Authorization') ?? $request->get('token');

        if ($token !== config('soketi.api_token')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $app = SoketiApp::where('id', $appId)->where('enabled', true)->first();

        if (!$app) {
            return response()->json(['error' => 'App not found'], 404);
        }

        return response()->json([
            'app' => [
                'id' => $app->id,
                'key' => $app->key,
                'secret' => $app->secret,
                'maxConnections' => $app->max_connections,
                'enableClientMessages' => $app->enable_client_messages,
                'enabled' => $app->enabled,
                'maxBackendEventsPerSecond' => $app->max_backend_events_per_sec,
                'maxClientEventsPerSecond' => $app->max_client_events_per_sec,
                'maxReadRequestsPerSecond' => $app->max_read_req_per_sec,
                'webhooks' => $app->webhooks,
                'maxPresenceMembersPerChannel' => $app->max_presence_members_per_channel,
                'maxPresenceMemberSizeInKb' => $app->max_presence_member_size_in_kb,
                'maxChannelNameLength' => $app->max_channel_name_length,
                'maxEventChannelsAtOnce' => $app->max_event_channels_at_once,
                'maxEventNameLength' => $app->max_event_name_length,
                'maxEventPayloadInKb' => $app->max_event_payload_in_kb,
                'maxEventBatchSize' => $app->max_event_batch_size,
                'enableUserAuthentication' => $app->enable_user_authentication,
            ]
        ]);
    }
}
