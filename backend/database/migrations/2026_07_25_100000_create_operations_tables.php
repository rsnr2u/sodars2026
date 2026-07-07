<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Resources (Wrapper)
        Schema::create('operations_resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('resource_type', 100); // vehicle, driver, employee, technician, equipment
            $table->uuid('external_id')->nullable(); // external aggregate reference identifier
            $table->string('display_name', 150);
            $table->json('skills'); // array of strings
            $table->json('availability_metadata')->nullable();
            $table->string('status', 30)->default('active'); // active, inactive
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'resource_type', 'external_id'], 'op_res_org_type_ext_idx');
        });

        // 2. Shifts
        Schema::create('operations_shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name', 150);
            $table->json('shift_pattern'); // start_time, end_time, rotation rule options
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
        });

        // 3. Calendars
        Schema::create('operations_calendars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('name', 150);
            $table->string('type', 50); // organization, state, branch, department, resource
            $table->string('timezone', 100)->default('UTC');
            $table->json('working_hours')->nullable();
            $table->json('holidays')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
        });

        // 4. Schedules (Planning Aggregate)
        Schema::create('operations_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('calendar_id')->nullable();
            $table->uuid('shift_id')->nullable();
            $table->string('schedule_number', 50)->unique();
            $table->string('name', 150);
            $table->string('schedule_type', 50); // driver_shift, route_dispatch, billboard_installation, etc.
            $table->string('status', 30)->default('draft');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->foreign('calendar_id')->references('id')->on('operations_calendars')->nullOnDelete();
            $table->foreign('shift_id')->references('id')->on('operations_shifts')->nullOnDelete();
        });

        // 5. Schedule Executions (Runtime Aggregate)
        Schema::create('operations_schedule_executions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('schedule_id');
            $table->string('execution_status', 30)->default('idle'); // idle, active, paused, completed, failed
            $table->dateTime('actual_start_time')->nullable();
            $table->dateTime('actual_end_time')->nullable();
            $table->dateTime('current_eta')->nullable();
            $table->bigInteger('actual_duration_seconds')->nullable();
            $table->decimal('actual_distance_meters', 12, 2)->nullable();
            $table->json('execution_metrics')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('schedule_id')->references('id')->on('operations_schedules')->onDelete('cascade');
            $table->index('organization_id');
        });

        // 6. Schedule Snapshots
        Schema::create('operations_schedule_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('schedule_id');
            $table->string('trigger_state', 30); // Approved, Optimized, Dispatched
            $table->json('snapshot_data');
            $table->timestamp('captured_at');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('schedule_id')->references('id')->on('operations_schedules')->onDelete('cascade');
        });

        // 7. Schedule Assignments
        Schema::create('operations_schedule_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('schedule_id');
            $table->uuid('resource_id');
            $table->timestamp('assigned_at');
            $table->timestamp('released_at')->nullable();
            $table->string('released_reason', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('schedule_id')->references('id')->on('operations_schedules')->onDelete('cascade');
            $table->foreign('resource_id')->references('id')->on('operations_resources')->onDelete('cascade');
            $table->index(['organization_id', 'schedule_id', 'resource_id'], 'op_sched_assign_org_sched_res_idx');
        });

        // 8. Schedule Conflicts
        Schema::create('operations_schedule_conflicts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('schedule_id');
            $table->string('conflict_type', 50);
            $table->string('severity', 30); // warning, critical
            $table->string('message', 255);
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->uuid('resolved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('schedule_id')->references('id')->on('operations_schedules')->onDelete('cascade');
        });

        // 9. Schedule Timelines
        Schema::create('operations_schedule_timelines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('schedule_id');
            $table->string('event_name', 100);
            $table->string('description', 255);
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('schedule_id')->references('id')->on('operations_schedules')->onDelete('cascade');
        });

        // 10. Resource State History
        Schema::create('operations_resource_state_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('resource_id');
            $table->string('state', 30); // Available, Assigned, Traveling, Waiting, Offline, Maintenance, Break, Leave
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('reason', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('resource_id', 'op_res_state_hist_res_id_fk')->references('id')->on('operations_resources')->onDelete('cascade');
            $table->index(['organization_id', 'resource_id', 'state'], 'op_res_state_hist_org_res_state_idx');
        });

        // 11. Schedule Participants
        Schema::create('operations_schedule_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('schedule_id');
            $table->string('participant_type', 150);
            $table->uuid('participant_id');
            $table->string('role', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('schedule_id')->references('id')->on('operations_schedules')->onDelete('cascade');
        });

        // 12. Schedule Checkpoints
        Schema::create('operations_schedule_checkpoints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('schedule_id');
            $table->string('name', 150);
            $table->integer('sequence');
            $table->string('status', 30)->default('pending');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->dateTime('reached_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('schedule_id')->references('id')->on('operations_schedules')->onDelete('cascade');
        });

        // 13. Recurrence Rules
        Schema::create('operations_recurrence_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('schedule_id');
            $table->string('frequency', 30); // daily, weekly, monthly
            $table->integer('interval')->default(1);
            $table->json('by_days')->nullable();
            $table->json('exception_dates')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('schedule_id')->references('id')->on('operations_schedules')->onDelete('cascade');
        });

        // 14. Read Projections
        // Availability
        Schema::create('operations_resource_availability_projections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('resource_id');
            $table->string('current_state', 30)->default('available');
            $table->json('blocked_time_slots')->nullable(); // array of [start, end]
            $table->dateTime('last_updated_at');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('resource_id', 'op_res_avail_proj_res_id_fk')->references('id')->on('operations_resources')->onDelete('cascade');
        });

        // Workload
        Schema::create('operations_resource_workload_projections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('resource_id');
            $table->integer('assigned_schedules_count')->default(0);
            $table->bigInteger('total_allocated_seconds')->default(0);
            $table->integer('utilization_score')->default(0); // 0-100%
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('resource_id', 'op_res_workload_res_id_fk')->references('id')->on('operations_resources')->onDelete('cascade');
        });

        // Dispatch Progress
        Schema::create('operations_dispatch_progress_projections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('schedule_id');
            $table->uuid('execution_id');
            $table->integer('completed_checkpoints_count')->default(0);
            $table->integer('total_checkpoints_count')->default(0);
            $table->integer('completion_percentage')->default(0);
            $table->dateTime('eta_estimate')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('schedule_id', 'op_disp_prog_sched_id_fk')->references('id')->on('operations_schedules')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations_dispatch_progress_projections');
        Schema::dropIfExists('operations_resource_workload_projections');
        Schema::dropIfExists('operations_resource_availability_projections');
        Schema::dropIfExists('operations_recurrence_rules');
        Schema::dropIfExists('operations_schedule_checkpoints');
        Schema::dropIfExists('operations_schedule_participants');
        Schema::dropIfExists('operations_resource_state_history');
        Schema::dropIfExists('operations_schedule_timelines');
        Schema::dropIfExists('operations_schedule_conflicts');
        Schema::dropIfExists('operations_schedule_assignments');
        Schema::dropIfExists('operations_schedule_snapshots');
        Schema::dropIfExists('operations_schedule_executions');
        Schema::dropIfExists('operations_schedules');
        Schema::dropIfExists('operations_calendars');
        Schema::dropIfExists('operations_shifts');
        Schema::dropIfExists('operations_resources');
    }
};
