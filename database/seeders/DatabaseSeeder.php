<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SoketiApp;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $adminUser = \App\Models\User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create demo user
        $demoUser = \App\Models\User::firstOrCreate(
            ['email' => 'demo@demo.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $apps = [
            [
                'id' => 'local-app',
                'app_name' => 'Local Development App',
                'app_description' => 'Application for local development environment',
                'key' => 'local-app-key',
                'secret' => 'local-app-secret',
                'max_connections' => 1000,
                'enable_client_messages' => true,
                'enabled' => true,
                'max_backend_events_per_sec' => 1000,
                'max_client_events_per_sec' => 100,
                'max_read_req_per_sec' => 1000,
                'webhooks' => json_encode([]),
                'max_presence_members_per_channel' => 100,
                'max_presence_member_size_in_kb' => 2,
                'max_channel_name_length' => 200,
                'max_event_channels_at_once' => 100,
                'max_event_name_length' => 200,
                'max_event_payload_in_kb' => 100,
                'max_event_batch_size' => 10,
                'enable_user_authentication' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'production-app',
                'app_name' => 'Production App',
                'app_description' => 'Application for production environment',
                'key' => Str::random(32),
                'secret' => Str::random(32),
                'max_connections' => 5000,
                'enable_client_messages' => true,
                'enabled' => true,
                'max_backend_events_per_sec' => 500,
                'max_client_events_per_sec' => 50,
                'max_read_req_per_sec' => 500,
                'webhooks' => json_encode([
                    [
                        'url' => 'https://your-app.com/webhooks/soketi',
                        'event_types' => ['channel_occupied', 'channel_vacated', 'member_added', 'member_removed']
                    ]
                ]),
                'max_presence_members_per_channel' => 50,
                'max_presence_member_size_in_kb' => 1,
                'max_channel_name_length' => 100,
                'max_event_channels_at_once' => 50,
                'max_event_name_length' => 100,
                'max_event_payload_in_kb' => 50,
                'max_event_batch_size' => 5,
                'enable_user_authentication' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($apps as $app) {
            \DB::table('soketi_apps')->updateOrInsert(
                ['id' => $app['id']],
                $app
            );
        }

        $this->command->info('🎉 Demo data created successfully!');
        $this->command->info('');
        $this->command->info('👤 Admin Login:');
        $this->command->info('   Email: admin@admin.com');
        $this->command->info('   Password: password');
        $this->command->info('');
        $this->command->info('👤 Demo Login:');
        $this->command->info('   Email: demo@demo.com');
        $this->command->info('   Password: password');
        $this->command->info('');
        $this->command->info('📱 Sample Apps Created:');
        $this->command->info('   - Main Application (production ready)');
        $this->command->info('   - Chat Application (with webhooks)');
        $this->command->info('   - Development App (inactive)');
    }
}
