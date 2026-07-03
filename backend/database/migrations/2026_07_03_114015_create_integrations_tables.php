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
        Schema::create('developer_api_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name');
            $table->string('key_prefix', 16);
            $table->string('secret_hash', 64);
            $table->json('scopes')->nullable(); // e.g. ['bookings:read', 'bookings:write']
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->string('last_user_agent', 255)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('webhook_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('target_url');
            $table->string('secret_token');
            $table->json('event_types');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('webhook_delivery_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('webhook_subscription_id');
            $table->string('event_type');
            $table->json('payload');
            $table->json('request_headers')->nullable();
            $table->json('response_headers')->nullable();
            $table->integer('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->integer('attempt')->default(1);
            $table->string('status', 30); // queued, processing, delivered, retrying, failed, cancelled
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('webhook_subscription_id')->references('id')->on('webhook_subscriptions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_delivery_logs');
        Schema::dropIfExists('webhook_subscriptions');
        Schema::dropIfExists('developer_api_keys');
    }
};
