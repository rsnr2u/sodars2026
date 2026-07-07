<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Workflow Definitions
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('key', 100)->unique(); // e.g. booking.approval, provider.verification
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Workflow Definition Versions
        Schema::create('workflow_definition_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('definition_id');
            $table->integer('version')->default(1);
            $table->json('dsl_schema'); // Full states, transitions, steps, guards definition
            $table->string('status', 30)->default('published'); // draft, published, archived
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('definition_id')->references('id')->on('workflow_definitions')->onDelete('cascade');
            $table->unique(['definition_id', 'version']);
        });

        // 3. Workflow Instances
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('definition_version_id');
            $table->string('entity_id'); // maps to aggregate_id
            $table->string('entity_type'); // maps to aggregate_type
            $table->uuid('organization_id')->nullable();
            $table->string('status', 50)->default('active'); // active, completed, terminated
            $table->string('current_state', 50)->default('Draft');
            $table->integer('current_step_index')->default(0);
            $table->json('dsl_snapshot'); // Complete DSL snapshot of the version at run time
            $table->json('context_snapshot')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('sla_status', 30)->default('normal'); // normal, warning, breached
            $table->timestamps();

            $table->foreign('definition_version_id')->references('id')->on('workflow_definition_versions')->onDelete('cascade');
            $table->index(['entity_type', 'entity_id']);
        });

        // 4. Workflow Tasks
        Schema::create('workflow_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('instance_id');
            $table->string('step_name', 150);
            $table->string('status', 50)->default('pending'); // pending, assigned, completed, cancelled, escalated
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('instance_id')->references('id')->on('workflow_instances')->onDelete('cascade');
        });

        // 5. Workflow Task Assignments
        Schema::create('workflow_task_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->string('assignment_type', 50); // user, role, team, permission, expression
            $table->string('assignment_value', 150);
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('workflow_tasks')->onDelete('cascade');
        });

        // 6. Workflow Histories (Transition and timeline logs)
        Schema::create('workflow_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('instance_id');
            $table->uuid('task_id')->nullable();
            $table->string('from_state', 50)->nullable();
            $table->string('to_state', 50);
            $table->string('action', 100); // start, approve_step, reject_step, complete, cancel, etc.
            $table->text('comments')->nullable();
            $table->uuid('actioned_by')->nullable();
            $table->timestamp('created_at');

            $table->foreign('instance_id')->references('id')->on('workflow_instances')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('workflow_tasks')->onDelete('set null');
        });

        // 7. Workflow Variables (Input values evaluated by guards and rules engine)
        Schema::create('workflow_variables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('instance_id');
            $table->string('name', 100);
            $table->string('value', 255);
            $table->string('type', 30)->default('string'); // string, integer, float, boolean
            $table->timestamps();

            $table->foreign('instance_id')->references('id')->on('workflow_instances')->onDelete('cascade');
            $table->unique(['instance_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_variables');
        Schema::dropIfExists('workflow_histories');
        Schema::dropIfExists('workflow_task_assignments');
        Schema::dropIfExists('workflow_tasks');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_definition_versions');
        Schema::dropIfExists('workflow_definitions');
    }
};
