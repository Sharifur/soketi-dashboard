<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Soketi Configuration Path
    |--------------------------------------------------------------------------
    |
    | The path to the Soketi configuration file.
    |
    */
    'config_path' => env('SOKETI_CONFIG_PATH', app_path() . '/soketi-config.json'),
    'token' => env('SOKETI_API_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Soketi API Token
    |--------------------------------------------------------------------------
    |
    | This token is used to authenticate requests from the Soketi server
    | to your Laravel application when using the API app manager.
    |
    */
    'api_token' => env('SOKETI_API_TOKEN', 'your-secure-api-token-here'),

    /*
    |--------------------------------------------------------------------------
    | Soketi Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to your Soketi server
    |
    */
    'server' => [
        'host' => env('SOKETI_HOST', env('PUSHER_HOST', '127.0.0.1')),
        'port' => env('SOKETI_PORT', env('PUSHER_PORT', 6001)),
        'scheme' => env('SOKETI_SCHEME', env('PUSHER_SCHEME', 'http')),
        'encrypted' => env('SOKETI_ENCRYPTED', env('PUSHER_ENCRYPTED', false)),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default App Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for new Soketi apps
    |
    */
    'defaults' => [
        'max_connections' => 1000,
        'enable_client_messages' => true,
        'max_backend_events_per_sec' => 100,
        'max_client_events_per_sec' => 100,
        'max_read_req_per_sec' => 100,
        'max_presence_members_per_channel' => 100,
        'max_presence_member_size_in_kb' => 2,
        'max_channel_name_length' => 200,
        'max_event_channels_at_once' => 100,
        'max_event_name_length' => 200,
        'max_event_payload_in_kb' => 100,
        'max_event_batch_size' => 10,
        'enable_user_authentication' => false,
    ],

    'app_url' => env('SOKETI_APP_URL', env('APP_URL', 'http://localhost:8000')),
];
