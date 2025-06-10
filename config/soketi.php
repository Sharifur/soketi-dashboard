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
    'config_path' => env('SOKETI_CONFIG_PATH', '/etc/soketi/config.json'),
    'token' => env('SOKETI_API_TOKEN', ''),
];
