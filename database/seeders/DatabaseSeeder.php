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

        // Create sample Soketi applications
        SoketiApp::create([
            'app_name' => 'Main Application',
            'app_description' => 'Primary Soketi application for production use',
            'key' => Str::random(16),
            'secret' => Str::random(16),
            'max_connections' => 1000,
            'enable_client_messages' => true,
            'enabled' => true,
            'max_backend_events_per_sec' => 100,
            'max_client_events_per_sec' => 50,
            'max_read_req_per_sec' => 100,
            'webhooks' => [],
            'max_presence_members_per_channel' => 100,
            'max_presence_member_size_in_kb' => 2,
            'max_channel_name_length' => 100,
            'max_event_channels_at_once' => 10,
            'max_event_name_length' => 100,
            'max_event_payload_in_kb' => 100,
            'max_event_batch_size' => 10,
            'enable_user_authentication' => true,
        ]);

        SoketiApp::create([
            'app_name' => 'Chat Application',
            'app_description' => 'Real-time chat application with presence channels',
            'key' => Str::random(16),
            'secret' => Str::random(16),
            'max_connections' => 500,
            'enable_client_messages' => true,
            'enabled' => true,
            'max_backend_events_per_sec' => 100,
            'max_client_events_per_sec' => 50,
            'max_read_req_per_sec' => 100,
            'webhooks' => [
                [
                    'url' => 'https://example.com/webhooks/chat',
                    'events' => ['channel_occupied', 'channel_vacated', 'member_added', 'member_removed']
                ]
            ],
            'max_presence_members_per_channel' => 100,
            'max_presence_member_size_in_kb' => 5,
            'max_channel_name_length' => 100,
            'max_event_channels_at_once' => 20,
            'max_event_name_length' => 100,
            'max_event_payload_in_kb' => 100,
            'max_event_batch_size' => 20,
            'enable_user_authentication' => true,
        ]);

        SoketiApp::create([
            'app_name' => 'Development App',
            'app_description' => 'Development and testing environment',
            'key' => Str::random(16),
            'secret' => Str::random(16),
            'max_connections' => 50,
            'enable_client_messages' => true,
            'enabled' => false,
            'max_backend_events_per_sec' => 50,
            'max_client_events_per_sec' => 20,
            'max_read_req_per_sec' => 50,
            'webhooks' => [],
            'max_presence_members_per_channel' => 50,
            'max_presence_member_size_in_kb' => 1,
            'max_channel_name_length' => 100,
            'max_event_channels_at_once' => 5,
            'max_event_name_length' => 100,
            'max_event_payload_in_kb' => 50,
            'max_event_batch_size' => 5,
            'enable_user_authentication' => false,
        ]);

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
