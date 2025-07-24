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
        Schema::table('soketi_apps', function (Blueprint $table) {
            // Add missing default values
            $table->integer('max_backend_events_per_sec')->default(100)->change();
            $table->integer('max_client_events_per_sec')->default(100)->change();
            $table->integer('max_read_req_per_sec')->default(100)->change();
            $table->integer('max_presence_members_per_channel')->default(100)->change();
            $table->integer('max_presence_member_size_in_kb')->default(2)->change();
            $table->integer('max_channel_name_length')->default(200)->change();
            $table->integer('max_event_channels_at_once')->default(100)->change();
            $table->integer('max_event_name_length')->default(200)->change();
            $table->integer('max_event_payload_in_kb')->default(100)->change();
            $table->integer('max_event_batch_size')->default(10)->change();
            $table->boolean('enable_user_authentication')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soketi_apps', function (Blueprint $table) {
            $table->string('key', 20)->nullable(false)->change();
            $table->string('secret', 40)->nullable(false)->change();
        });
    }
};
