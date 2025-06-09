<?php

namespace App\Services;

use App\Models\SoketiApp;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SoketiConfigService
{
    protected string $configPath;

    public function __construct()
    {
        $this->configPath = config('soketi.config_path', '/etc/soketi/config.json');
    }

    /**
     * Sync all active apps to Soketi config.json
     */
    public function syncToConfig(): bool
    {
        try {
            $apps = SoketiApp::where('is_active', true)->get();

            $config = [
                'debug' => config('app.debug', false),
                'port' => 6001,
                'metrics' => [
                    'enabled' => true,
                    'port' => 9601,
                ],
                'appManager' => [
                    'driver' => 'array',
                    'array' => [
                        'apps' => $apps->map(function ($app) {
                            return [
                                'id' => $app->app_id,
                                'key' => $app->app_key,
                                'secret' => $app->app_secret,
                                'name' => $app->name,
                                'maxConnections' => $app->max_connections,
                                'enableClientMessages' => $app->enable_client_messages,
                                'enableStats' => $app->enable_statistics,
                                'webhooks' => $this->formatWebhooks($app),
                            ];
                        })->toArray()
                    ]
                ],
                'queue' => [
                    'driver' => 'sync',
                ],
                'rateLimiter' => [
                    'driver' => 'local',
                ],
            ];

            return $this->writeConfig($config);
        } catch (\Exception $e) {
            Log::error('Failed to sync to Soketi config: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Read apps from Soketi config.json and sync to database
     */
    public function syncFromConfig(): array
    {
        $result = ['created' => 0, 'updated' => 0];

        try {
            $config = $this->readConfig();
            $configApps = $config['appManager']['array']['apps'] ?? [];

            foreach ($configApps as $configApp) {
                $app = SoketiApp::where('app_id', $configApp['id'])->first();

                $appData = [
                    'app_id' => $configApp['id'],
                    'app_key' => $configApp['key'],
                    'app_secret' => $configApp['secret'],
                    'name' => $configApp['name'] ?? "App {$configApp['id']}",
                    'is_active' => true,
                    'max_connections' => $configApp['maxConnections'] ?? 500,
                    'enable_client_messages' => $configApp['enableClientMessages'] ?? true,
                    'enable_statistics' => $configApp['enableStats'] ?? true,
                ];

                if ($app) {
                    $app->update($appData);
                    $result['updated']++;
                } else {
                    SoketiApp::create($appData);
                    $result['created']++;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync from Soketi config: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Read config.json file
     */
    protected function readConfig(): array
    {
        if (!File::exists($this->configPath)) {
            return ['appManager' => ['array' => ['apps' => []]]];
        }

        $content = File::get($this->configPath);
        return json_decode($content, true) ?? [];
    }

    /**
     * Write config.json file
     */
    protected function writeConfig(array $config): bool
    {
        try {
            // Create directory if it doesn't exist
            $dir = dirname($this->configPath);
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            // Create backup
            if (File::exists($this->configPath)) {
                File::copy($this->configPath, $this->configPath . '.backup');
            }

            // Write new config
            $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            File::put($this->configPath, $json);

            Log::info('Soketi config updated successfully');
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to write Soketi config: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format webhooks for config
     */
    protected function formatWebhooks(SoketiApp $app): array
    {
        if (!$app->enable_webhooks || empty($app->webhook_urls)) {
            return [];
        }

        $webhooks = [];
        $events = $app->webhook_events ?? ['channel_occupied', 'channel_vacated'];

        foreach ($app->webhook_urls as $webhook) {
            foreach ($events as $event) {
                $webhooks[$event] = [
                    'url' => $webhook['url'],
                    'headers' => $app->webhook_headers ?? [],
                ];
            }
        }

        return $webhooks;
    }
}
