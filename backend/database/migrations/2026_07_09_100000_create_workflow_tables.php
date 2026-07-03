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
            $table->string('key', 100); // e.g. booking.approval, provider.verification
            $table->integer('version')->default(1);
            $table->string('entity_type'); // FQCN of the entity (e.g. App\Modules\Bookings\Domain\Entities\Booking)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['key', 'version']);
        });

        // 2. Workflow Definition Steps
        Schema::create('workflow_definition_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('definition_id');
            $table->string('name', 150);
            $table->string('role', 100); // role assigned to perform this step (e.g. branch_manager, super_admin)
            $table->integer('order'); // step execution order (1, 2, 3...)
            $table->integer('sla_hours')->default(24);
            $table->string('approval_mode', 20)->default('any'); // any, all
            $table->string('step_type', 50)->default('approval'); // approval, notification, etc.
            $table->timestamps();

            $table->foreign('definition_id')->references('id')->on('workflow_definitions')->onDelete('cascade');
        });

        // 3. Workflow Instances
        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('definition_id');
            $table->string('entity_id');
            $table->string('entity_type');
            $table->string('status', 50)->default('draft'); // draft, active, completed, cancelled, terminated
            $table->integer('current_step_index')->default(0);
            $table->json('context_snapshot')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('definition_id')->references('id')->on('workflow_definitions')->onDelete('cascade');
            $table->index(['entity_type', 'entity_id']);
        });

        // 4. Workflow Tasks
        Schema::create('workflow_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('instance_id');
            $table->uuid('step_id');
            $table->string('status', 50)->default('pending'); // pending, assigned, in_progress, approved, rejected, delegated, escalated, cancelled
            $table->string('assigned_role', 100);
            $table->uuid('assigned_user_id')->nullable();
            $table->uuid('actioned_by')->nullable();
            $table->timestamp('actioned_at')->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->string('escalated_to_role', 100)->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamps();

            $table->foreign('instance_id')->references('id')->on('workflow_instances')->onDelete('cascade');
            $table->foreign('step_id')->references('id')->on('workflow_definition_steps')->onDelete('cascade');
        });

        // 5. Workflow Histories (Transition History)
        Schema::create('workflow_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('instance_id');
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50);
            $table->string('action', 100); // start, approve_step, reject_step, escalate, complete, cancel, etc.
            $table->text('comments')->nullable();
            $table->uuid('actioned_by')->nullable();
            $table->timestamp('created_at');

            $table->foreign('instance_id')->references('id')->on('workflow_instances')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_histories');
        Schema::dropIfExists('workflow_tasks');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_definition_steps');
        Schema::dropIfExists('workflow_definitions');
    }
};
