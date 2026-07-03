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
        Schema::create('dashboards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->json('layout_config')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('dashboard_id');
            $table->string('report_key');
            $table->string('widget_type');
            $table->string('title');
            $table->json('dimensions');
            $table->json('query_parameters')->nullable();
            $table->string('drilldown_route')->nullable();
            $table->timestamps();

            $table->foreign('dashboard_id')->references('id')->on('dashboards')->cascadeOnDelete();
        });

        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('report_key');
            $table->string('name');
            $table->string('cron_expression');
            $table->json('query_parameters')->nullable();
            $table->json('recipient_emails');
            $table->string('export_format')->default('csv');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('report_executions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('scheduled_report_id')->nullable();
            $table->string('report_key');
            $table->string('status');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->uuid('dam_asset_id')->nullable();
            $table->text('error_message')->nullable();
            $table->uuid('executed_by')->nullable();
            $table->json('context_snapshot')->nullable();
            $table->timestamps();

            $table->foreign('executed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_executions');
        Schema::dropIfExists('scheduled_reports');
        Schema::dropIfExists('dashboard_widgets');
        Schema::dropIfExists('dashboards');
    }
};
