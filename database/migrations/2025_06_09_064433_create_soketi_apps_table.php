<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('soketi_apps', function (Blueprint $table) {
            $table->string('id')->primary(); // Matches: varchar(255) PRIMARY KEY
            $table->string('app_name'); // Matches: varchar(255) PRIMARY KEY
            $table->string('app_description')->nullable(); // Matches: varchar(255) PRIMARY KEY
            $table->string('key');           // Matches: varchar(255)
            $table->string('secret');        // Matches: varchar(255)

            $table->integer('max_connections')->default(1000);              // Matches: integer(10)
            $table->boolean('enable_client_messages')->default(true);       // Matches: tinyint(1)
            $table->boolean('enabled')->default(true);                      // Matches: tinyint(1)
            $table->integer('max_backend_events_per_sec');   // Matches: integer(10)
            $table->integer('max_client_events_per_sec');    // Matches: integer(10)
            $table->integer('max_read_req_per_sec');         // Matches: integer(10)

            $table->json('webhooks')->nullable();            // Matches: json

            // Nullable optional tinyint(1) columns
            $table->tinyInteger('max_presence_members_per_channel')->nullable();
            $table->tinyInteger('max_presence_member_size_in_kb')->nullable();
            $table->tinyInteger('max_channel_name_length')->nullable();
            $table->tinyInteger('max_event_channels_at_once')->nullable();
            $table->tinyInteger('max_event_name_length')->nullable();
            $table->tinyInteger('max_event_payload_in_kb')->nullable();
            $table->tinyInteger('max_event_batch_size')->nullable();

            $table->boolean('enable_user_authentication');   // Matches: tinyint(1)
        });

        Schema::create('soketi_connections', function (Blueprint $table) {
            $table->id();
            $table->string('connection_id')->unique();
            $table->string('app_id');
            $table->string('socket_id')->nullable();
            $table->string('channel_name')->nullable();
            $table->json('user_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_connected')->default(true);
            $table->timestamp('connected_at');
            $table->timestamp('disconnected_at')->nullable();
            $table->timestamps();

            $table->foreign('app_id')->references('app_id')->on('soketi_apps')->onDelete('cascade');
            $table->index(['app_id', 'is_connected']);
            $table->index(['connected_at', 'disconnected_at']);
        });

        Schema::create('soketi_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('app_id');
            $table->string('event_name');
            $table->string('webhook_url');
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->integer('attempts')->default(0);
            $table->text('response_body')->nullable();
            $table->integer('response_status')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();

            $table->foreign('app_id')->references('app_id')->on('soketi_apps')->onDelete('cascade');
            $table->index(['app_id', 'status']);
            $table->index(['event_name', 'sent_at']);
        });

        Schema::create('soketi_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('app_id');
            $table->string('metric_name');
            $table->string('metric_value');
            $table->json('metadata')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->foreign('app_id')->references('app_id')->on('soketi_apps')->onDelete('cascade');
            $table->index(['app_id', 'metric_name', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soketi_statistics');
        Schema::dropIfExists('soketi_webhooks');
        Schema::dropIfExists('soketi_connections');
        Schema::dropIfExists('soketi_apps');
    }
};
