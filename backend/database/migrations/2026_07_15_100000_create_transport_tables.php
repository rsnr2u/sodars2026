<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fleets
        Schema::create('fleets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->string('name', 150);
            $table->string('code', 50)->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
        });

        // 2. Vehicles
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->uuid('fleet_id')->nullable();
            $table->string('vehicle_number', 50)->nullable();
            $table->string('license_plate', 50);
            $table->string('make', 100);
            $table->string('model', 100);
            $table->integer('year');
            $table->string('status', 30)->default('active'); // active, maintenance, inactive
            $table->integer('current_odometer')->default(0);

            // Capacity
            $table->decimal('payload_capacity', 10, 2)->nullable();
            $table->decimal('volume_capacity', 10, 2)->nullable();
            $table->integer('number_of_screens')->nullable();
            $table->integer('max_billboards')->nullable();

            // AI/Predictive
            $table->decimal('predicted_failure_probability', 5, 2)->nullable();
            $table->decimal('maintenance_risk_score', 5, 2)->nullable();
            $table->decimal('fuel_efficiency_score', 5, 2)->nullable();
            $table->decimal('vehicle_health_score', 5, 2)->nullable();
            $table->date('predicted_maintenance_date')->nullable();
            $table->bigInteger('predicted_fuel_cost')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('fleet_id')->references('id')->on('fleets')->nullOnDelete();
        });

        // 3. Drivers
        Schema::create('drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->string('driver_number', 50)->nullable();
            $table->uuid('user_id')->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('license_number', 100);
            $table->string('license_class', 50);
            $table->date('license_expiry');
            $table->date('medical_expiry')->nullable();
            $table->string('badge_number', 100)->nullable();
            $table->string('employment_status', 30)->default('full_time'); // full_time, part_time, contractor
            $table->date('joining_date');
            $table->string('emergency_contact', 150)->nullable();
            $table->string('emergency_phone', 30)->nullable();
            $table->date('background_check_date')->nullable();
            $table->date('background_check_expiry')->nullable();
            $table->boolean('training_completed')->default(false);
            $table->date('training_expiry')->nullable();
            $table->string('status', 30)->default('active'); // active, suspended, inactive

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        // 4. Vehicle Maintenance Logs
        Schema::create('vehicle_maintenances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->uuid('vehicle_id');
            $table->string('maintenance_type', 50); // routine, repair, inspection
            $table->text('description')->nullable();
            $table->bigInteger('cost_cents')->default(0);
            $table->date('maintenance_date');
            $table->integer('odometer_reading');
            $table->string('status', 30)->default('Scheduled'); // Scheduled, In Progress, Completed, Cancelled
            $table->date('next_due_date')->nullable();
            $table->integer('next_due_odometer')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });

        // 5. Vehicle Fuel Logs
        Schema::create('vehicle_fuel_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->uuid('vehicle_id');
            $table->date('fuel_date');
            $table->decimal('liters', 10, 2);
            $table->bigInteger('cost_cents')->default(0);
            $table->integer('odometer_reading');
            $table->string('fuel_station', 150)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('filled_by', 100)->nullable();
            $table->string('receipt_number', 100)->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });

        // 6. Vehicle Assignments
        Schema::create('vehicle_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->uuid('vehicle_id');
            $table->uuid('driver_id');
            $table->dateTime('assigned_from');
            $table->dateTime('assigned_to')->nullable();
            $table->string('reason', 200)->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
        });

        // 7. Vehicle GPS Telemetry Stream (decoupled from aggregate)
        Schema::create('vehicle_gps_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->uuid('vehicle_id');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('speed_kmh', 5, 2);
            $table->decimal('heading', 5, 2)->nullable();
            $table->decimal('altitude', 8, 2)->nullable();
            $table->decimal('accuracy', 4, 2)->nullable();
            $table->string('engine_status', 30)->nullable();
            $table->string('ignition_status', 30)->nullable();
            $table->decimal('battery_voltage', 4, 2)->nullable();
            $table->integer('satellite_count')->nullable();
            $table->dateTime('recorded_at');

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->index(['vehicle_id', 'recorded_at']);
        });

        // 8. Routes
        Schema::create('routes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->string('route_number', 50)->nullable();
            $table->uuid('vehicle_id')->nullable();
            $table->uuid('driver_id')->nullable();
            
            // Context integrations
            $table->uuid('booking_id')->nullable();
            $table->uuid('booking_item_id')->nullable();
            $table->uuid('campaign_id')->nullable();
            $table->uuid('inventory_reservation_id')->nullable();

            $table->string('start_location', 250);
            $table->string('end_location', 250);
            $table->decimal('planned_distance_km', 10, 2)->nullable();
            $table->integer('planned_duration_minutes')->nullable();
            $table->decimal('actual_distance_km', 10, 2)->nullable();
            $table->integer('actual_duration_minutes')->nullable();
            $table->string('status', 30)->default('Draft'); // Draft, Planned, Assigned, Dispatched, In Transit, Arrived, Completed, Archived, Cancelled, Paused, Delayed
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->nullOnDelete();
            $table->foreign('driver_id')->references('id')->on('drivers')->nullOnDelete();
            $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();
            $table->foreign('campaign_id')->references('id')->on('campaigns')->nullOnDelete();
        });

        // 9. Route Stops
        Schema::create('route_stops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->nullable();
            $table->uuid('route_id');
            $table->string('stop_name', 250);
            $table->integer('sequence_number');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('status', 30)->default('pending'); // pending, arrived, completed, skipped
            $table->dateTime('arrived_at')->nullable();
            $table->dateTime('departed_at')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_stops');
        Schema::dropIfExists('routes');
        Schema::dropIfExists('vehicle_gps_logs');
        Schema::dropIfExists('vehicle_assignments');
        Schema::dropIfExists('vehicle_fuel_logs');
        Schema::dropIfExists('vehicle_maintenances');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('fleets');
    }
};
