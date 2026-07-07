<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Unified Scheduled Jobs Table
        Schema::create('scheduled_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->string('category', 50); // e.g. workflow, notification, integration, webhook
            $table->string('job_type', 100); // e.g. timeout, escalation, retry, email
            $table->string('aggregate_type', 150)->nullable();
            $table->string('aggregate_id', 100)->nullable();
            $table->timestamp('execute_at');
            $table->string('status', 30)->default('pending'); // pending, processing, completed, failed, cancelled
            $table->json('payload');
            $table->integer('attempts')->default(0);
            $table->json('retry_policy')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('triggered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('correlation_id', 100)->nullable();
            $table->string('trace_id', 100)->nullable();
            $table->timestamps();

            $table->index(['status', 'execute_at']);
            $table->index(['aggregate_type', 'aggregate_id']);
        });

        // 2. Parallel Gateway Workflow Execution Tokens Table
        Schema::create('workflow_execution_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workflow_instance_id');
            $table->string('gateway_id', 100);
            $table->string('branch_name', 100);
            $table->string('status', 30)->default('active'); // active, completed
            $table->timestamp('created_at');
            $table->timestamp('completed_at')->nullable();

            $table->foreign('workflow_instance_id')->references('id')->on('workflow_instances')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_execution_tokens');
        Schema::dropIfExists('scheduled_jobs');
    }
};
