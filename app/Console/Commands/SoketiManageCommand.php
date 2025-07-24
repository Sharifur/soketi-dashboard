<?php

namespace App\Console\Commands;

use App\Models\SoketiApp;
use App\Services\SoketiStatsService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SoketiManageCommand extends Command
{
    protected $signature = 'soketi:manage
                            {action : The action to perform (create|list|test|cleanup|stats)}
                            {--name= : App name for create action}
                            {--description= : App description for create action}
                            {--id= : App ID for specific actions}
                            {--days= : Number of days for cleanup (default: 7)}';

    protected $description = 'Manage Soketi applications and data';

    protected SoketiStatsService $statsService;

    public function __construct(SoketiStatsService $statsService)
    {
        parent::__construct();
        $this->statsService = $statsService;
    }

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'create' => $this->createApp(),
            'list' => $this->listApps(),
            'test' => $this->testConnection(),
            'cleanup' => $this->cleanup(),
            'stats' => $this->showStats(),
            default => $this->error("Unknown action: {$action}"),
        };
    }

    protected function createApp(): int
    {
        $name = $this->option('name') ?: $this->ask('Enter app name');
        $description = $this->option('description') ?: $this->ask('Enter app description (optional)', '');

        if (!$name) {
            $this->error('App name is required');
            return 1;
        }

        $app = SoketiApp::create([
            'app_name' => $name,
            'app_description' => $description,
        ]);

        $this->info("✅ Created Soketi app successfully!");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $app->id],
                ['Name', $app->app_name],
                ['Key', $app->key],
                ['Secret', $app->secret],
                ['Max Connections', $app->max_connections],
                ['Enabled', $app->enabled ? 'Yes' : 'No'],
            ]
        );

        return 0;
    }

    protected function listApps(): int
    {
        $apps = SoketiApp::all();

        if ($apps->isEmpty()) {
            $this->info('No Soketi apps found');
            return 0;
        }

        $data = $apps->map(function ($app) {
            return [
                'ID' => $app->id,
                'Name' => $app->app_name,
                'Status' => $app->enabled ? '✅ Enabled' : '❌ Disabled',
                'Connections' => $app->getCurrentConnectionCount() . '/' . $app->max_connections,
                'Utilization' => $app->getConnectionUtilization() . '%',
                'Created' => $app->created_at->format('Y-m-d H:i'),
            ];
        })->toArray();

        $this->table(
            ['ID', 'Name', 'Status', 'Connections', 'Utilization', 'Created'],
            $data
        );

        return 0;
    }

    protected function testConnection(): int
    {
        $this->info('Testing Soketi server connection...');

        $result = $this->statsService->testConnection();

        if ($result['success']) {
            $this->info("✅ Soketi server is online");
            $this->info("Response time: {$result['response_time']}ms");
        } else {
            $this->error("❌ Soketi server is offline");
            $this->error("Error: {$result['message']}");
            return 1;
        }

        $appId = $this->option('id');
        if ($appId) {
            $app = SoketiApp::find($appId);
            if (!$app) {
                $this->error("App with ID {$appId} not found");
                return 1;
            }

            $this->info("\nApp Statistics for: {$app->app_name}");
            $stats = $this->statsService->getAppStats($app);

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Current Connections', $stats['current_connections']],
                    ['Max Connections', $stats['max_connections']],
                    ['Utilization', $stats['utilization_percentage'] . '%'],
                    ['Connections (24h)', $stats['connections_last_24h']],
                ]
            );
        }

        return 0;
    }

    protected function cleanup(): int
    {
        $days = (int)($this->option('days') ?: 7);

        $this->info("Cleaning up Soketi data older than {$days} days...");

        $result = $this->statsService->cleanup($days);

        $this->info("✅ Cleanup completed:");
        $this->info("- Deleted {$result['deleted_connections']} old connections");
        $this->info("- Deleted {$result['deleted_events']} old events");
        $this->info("- Cutoff date: {$result['cutoff_date']}");

        return 0;
    }

    protected function showStats(): int
    {
        $stats = $this->statsService->getSystemStats();

        $this->info('📊 Soketi System Statistics');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Apps', $stats['total_apps']],
                ['Active Apps', $stats['active_apps']],
                ['Inactive Apps', $stats['inactive_apps']],
                ['Active Connections', $stats['total_active_connections']],
                ['Server Status', $stats['server_status']['success'] ? '✅ Online' : '❌ Offline'],
            ]
        );

        if ($this->option('id')) {
            $app = SoketiApp::find($this->option('id'));
            if ($app) {
                $this->info("\nDetailed stats for: {$app->app_name}");
                $appStats = $this->statsService->getAppStats($app);
                $eventStats = $this->statsService->getEventStats($app);

                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Current Connections', $appStats['current_connections']],
                        ['Total Connections', $appStats['total_connections']],
                        ['Connections (24h)', $appStats['connections_last_24h']],
                        ['Events (24h)', $eventStats['events_last_24h']],
                        ['Utilization', $appStats['utilization_percentage'] . '%'],
                    ]
                );

                if (!empty($eventStats['events_by_type'])) {
                    $this->info("\nEvent breakdown (24h):");
                    foreach ($eventStats['events_by_type'] as $type => $count) {
                        $this->info("- " . ucfirst($type) . ": {$count}");
                    }
                }
            }
        }

        return 0;
    }
}
