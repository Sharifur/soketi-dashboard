<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soketi_debug_events', function (Blueprint $table) {
            $table->id();
            $table->string('app_id');
            $table->string('event_type'); // connection, subscription, message, etc.
            $table->string('channel')->nullable();
            $table->string('event_name')->nullable();
            $table->json('payload')->nullable();
            $table->string('socket_id')->nullable();
            $table->string('user_id')->nullable();
            $table->timestamp('timestamp');
            $table->timestamps();

            $table->foreign('app_id')->references('id')->on('soketi_apps')->onDelete('cascade');
            $table->index(['app_id', 'event_type', 'timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soketi_debug_events');
    }
};
