<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Devices table (Aggregate Root)
        Schema::create('devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('device_number', 50)->unique();
            $table->string('serial_number', 100)->unique();
            $table->string('name', 150);
            $table->string('device_type', 50);
            $table->string('status', 30);
            $table->string('imei', 50)->nullable();
            $table->string('iccid', 50)->nullable();
            $table->string('mac_address', 50)->nullable();
            $table->string('manufacturer', 100)->nullable();
            $table->string('hardware_revision', 50)->nullable();
            $table->string('firmware_version', 50)->nullable();
            $table->string('device_secret', 256);
            $table->timestamp('last_seen_at')->nullable();
            $table->uuid('current_configuration_version_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
        });

        // 2. Device Assignments (Temporal history log)
        Schema::create('device_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('device_id');
            $table->string('assignable_type', 150);
            $table->uuid('assignable_id');
            $table->timestamp('assigned_at');
            $table->timestamp('released_at')->nullable();
            $table->string('released_reason', 255)->nullable();
            $table->uuid('assigned_by');
            $table->uuid('released_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
            $table->index(['assignable_type', 'assignable_id']);
        });

        // 3. Device Configuration Versions
        Schema::create('device_configuration_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('device_id');
            $table->integer('version');
            $table->json('configuration');
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });

        // 4. Device Heartbeats
        Schema::create('device_heartbeats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('device_id');
            $table->timestamp('received_at');
            $table->string('ip_address', 45);
            $table->string('firmware_version', 50);
            $table->integer('signal_quality_dbm');
            $table->integer('battery_level_percent');
            $table->bigInteger('uptime_seconds');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });

        // 5. Device Commands
        Schema::create('device_commands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('device_id');
            $table->uuid('command_uuid')->unique();
            $table->string('idempotency_key', 150)->unique();
            $table->string('correlation_id', 100)->nullable();
            $table->string('command_type', 100);
            $table->string('status', 30);
            $table->json('payload');
            $table->integer('attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });

        // 6. Firmware Packages
        Schema::create('firmware_packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('version', 50);
            $table->string('sha256', 64);
            $table->bigInteger('size_bytes');
            $table->text('signature');
            $table->string('signature_algorithm', 50);
            $table->string('download_url', 255);
            $table->string('min_supported_version', 50)->nullable();
            $table->string('max_supported_version', 50)->nullable();
            $table->json('compatible_device_types');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. Device Firmware Installations
        Schema::create('device_firmware_installations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('device_id');
            $table->uuid('firmware_package_id');
            $table->string('status', 30);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('rollback_from', 50)->nullable();
            $table->string('rollback_to', 50)->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
            $table->foreign('firmware_package_id')->references('id')->on('firmware_packages')->onDelete('cascade');
        });

        // 8. Device Alerts
        Schema::create('device_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('device_id');
            $table->string('alert_type', 100);
            $table->string('severity', 30);
            $table->string('message', 255);
            $table->timestamp('raised_at');
            $table->timestamp('resolved_at')->nullable();
            $table->uuid('resolved_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });

        // 9. Device Telemetry Logs
        Schema::create('device_telemetry_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('device_id');
            $table->timestamp('logged_at');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('speed_kph', 5, 2)->nullable();
            $table->integer('heading_degrees')->nullable();
            $table->json('diagnostics');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });

        // 10. Device Health Snapshots
        Schema::create('device_health_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('device_id');
            $table->integer('cpu_usage_percent')->nullable();
            $table->integer('memory_usage_percent')->nullable();
            $table->integer('disk_usage_percent')->nullable();
            $table->integer('temperature_celsius')->nullable();
            $table->integer('battery_level_percent')->nullable();
            $table->integer('signal_quality_dbm')->nullable();
            $table->integer('overall_health_score')->default(100);
            $table->integer('battery_score')->default(100);
            $table->integer('signal_score')->default(100);
            $table->integer('temperature_score')->default(100);
            $table->integer('storage_score')->default(100);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_health_snapshots');
        Schema::dropIfExists('device_telemetry_logs');
        Schema::dropIfExists('device_alerts');
        Schema::dropIfExists('device_firmware_installations');
        Schema::dropIfExists('firmware_packages');
        Schema::dropIfExists('device_commands');
        Schema::dropIfExists('device_heartbeats');
        Schema::dropIfExists('device_configuration_versions');
        Schema::dropIfExists('device_assignments');
        Schema::dropIfExists('devices');
    }
};
