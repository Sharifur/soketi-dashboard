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
            'name' => 'Main Application',
            'app_id' => random_int(000000000,999999999),
            'app_key' => Str::random(16),
            'app_secret' =>  Str::random(16),
            'description' => 'Primary Soketi application for production use',
            'max_connections' => 1000,
            'enable_client_messages' => true,
            'enable_statistics' => true,
            'enable_webhooks' => false,
            'is_active' => true,
            'user_id' => $adminUser->id,
        ]);

        SoketiApp::create([
            'name' => 'Chat Application',
            'app_id' => random_int(000000000,999999999),
            'app_key' => Str::random(16),
            'app_secret' =>  Str::random(16),
            'description' => 'Real-time chat application with presence channels',
            'max_connections' => 500,
            'enable_client_messages' => true,
            'enable_statistics' => true,
            'enable_webhooks' => true,
            'webhook_urls' => [
                ['url' => 'https://example.com/webhooks/soketi']
            ],
            'webhook_events' => ['channel_occupied', 'channel_vacated', 'member_added', 'member_removed'],
            'is_active' => true,
            'user_id' => $adminUser->id,
        ]);

        SoketiApp::create([
            'name' => 'Notification Service',
            'app_id' => random_int(000000000,999999999),
            'app_key' => Str::random(16),
            'app_secret' =>  Str::random(16),
            'description' => 'Push notifications and alerts system',
            'max_connections' => 250,
            'enable_client_messages' => false,
            'enable_statistics' => true,
            'enable_webhooks' => true,
            'webhook_urls' => [
                ['url' => 'https://example.com/webhooks/notifications']
            ],
            'webhook_headers' => [
                'Authorization' => 'Bearer your-token-here',
                'X-App-Version' => '1.0'
            ],
            'webhook_events' => ['channel_occupied', 'client_event'],
            'is_active' => true,
            'user_id' => $demoUser->id,
        ]);

        SoketiApp::create([
            'name' => 'Development App',
            'app_id' => random_int(000000000,999999999),
            'app_key' => Str::random(16),
            'app_secret' =>  Str::random(16),
            'description' => 'Development and testing environment',
            'max_connections' => 50,
            'enable_client_messages' => true,
            'enable_statistics' => true,
            'enable_webhooks' => false,
            'is_active' => false,
            'user_id' => $demoUser->id,
        ]);

        SoketiApp::create([
            'name' => 'Analytics Dashboard',
            'app_id' => random_int(000000000,999999999),
            'app_key' => Str::random(16),
            'app_secret' =>  Str::random(16),
            'description' => 'Real-time analytics and metrics visualization',
            'max_connections' => 100,
            'enable_client_messages' => false,
            'enable_statistics' => true,
            'enable_webhooks' => true,
            'webhook_urls' => [
                ['url' => 'https://analytics.example.com/webhook'],
                ['url' => 'https://backup-analytics.example.com/webhook']
            ],
            'webhook_events' => ['channel_occupied', 'channel_vacated'],
            'is_active' => true,
            'user_id' => $adminUser->id,
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
        $this->command->info('   - Notification Service (push notifications)');
        $this->command->info('   - Development App (inactive)');
        $this->command->info('   - Analytics Dashboard (metrics)');
    }
}
