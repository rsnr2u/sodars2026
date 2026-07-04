<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('category', 50); // auth, data_change, workflow, integration, security, etc.
            $table->string('event_type', 100);
            $table->integer('event_version')->default(1);
            $table->timestamp('occurred_at');
            $table->string('auditable_type')->nullable();
            $table->string('auditable_id', 36)->nullable();
            $table->json('before_snapshot')->nullable();
            $table->json('after_snapshot')->nullable();
            $table->text('description');
            $table->string('risk_level', 20); // low, medium, high, critical
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('device_type', 30)->nullable();
            $table->uuid('trace_id')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->uuid('request_id')->nullable();
            $table->uuid('session_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('event_type');
            $table->index('category');
            $table->index('risk_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
    }
};
