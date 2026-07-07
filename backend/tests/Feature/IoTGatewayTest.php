<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\IoT\Domain\Entities\Device;
use App\Modules\IoT\Domain\Entities\DeviceAssignment;
use App\Modules\IoT\Domain\Entities\DeviceHealthSnapshot;
use App\Modules\IoT\Domain\Entities\DeviceTelemetryLog;
use App\Modules\IoT\Domain\Entities\DeviceHeartbeat;
use App\Modules\IoT\Domain\Entities\DeviceCommand;
use App\Modules\IoT\Domain\Entities\DeviceAlert;
use App\Modules\IoT\Domain\Entities\FirmwarePackage;
use App\Modules\IoT\Domain\Entities\DeviceFirmwareInstallation;
use App\Modules\IoT\Domain\Enums\DeviceStatus;
use App\Modules\IoT\Domain\Enums\DeviceType;
use App\Modules\IoT\Domain\Enums\CommandStatus;
use App\Modules\IoT\Domain\Enums\FirmwareInstallationStatus;
use App\Modules\IoT\Domain\Services\DeviceLifecycleService;
use App\Modules\IoT\Domain\Services\HmacAuthenticator;
use App\Modules\IoT\Domain\Services\TelemetryProcessor;
use App\Modules\Transport\Domain\Entities\Vehicle;
use App\Modules\Transport\Domain\Entities\VehicleGPSLog;
use App\Modules\Branches\Domain\Entities\Branch;
use App\Platform\Identity\Domain\Entities\Organization;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Platform\Scheduler\Application\Services\SchedulerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class IoTGatewayTest extends ApiTestCase
{
    use RefreshDatabase;

    protected DeviceLifecycleService $service;
    protected string $orgId;
    protected Device $device;
    protected Vehicle $vehicle;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DeviceLifecycleService::class);

        // Create active Organization to fulfill foreign key requirements
        $org = Organization::create([
            'id' => (string) Str::uuid(),
            'name' => 'IoT Corp',
            'slug' => 'iot',
            'subdomain' => 'iot',
            'status' => 'active',
        ]);
        $this->orgId = $org->id;

        // 1. Create a branch for transport setup
        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'name' => 'IoT Branch',
            'code' => 'IOT-B',
            'support_email' => 'iot@sodars.com',
            'support_phone' => '+919999',
        ]);

        // 2. Create a vehicle for tracking
        $this->vehicle = Vehicle::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $this->orgId,
            'vehicle_number' => 'VEH-IOT-999',
            'license_plate' => 'DL-999-IOT',
            'make' => 'Tata',
            'model' => 'Ace',
            'year' => 2026,
            'status' => \App\Modules\Transport\Domain\Enums\VehicleStatus::Active,
        ]);

        // 3. Register a test IoT device
        $this->device = $this->service->registerDevice([
            'organization_id' => $this->orgId,
            'serial_number' => 'IOT-DEV-SERIAL-100',
            'name' => 'Route GPS Tracker 100',
            'device_type' => DeviceType::GpsTracker->value,
            'device_secret' => 'super_secret_device_passphrase',
        ]);
    }

    public function test_hmac_authentication_verification(): void
    {
        $authenticator = app(HmacAuthenticator::class);
        $timestamp = (string) time();
        $nonce = Str::random(16);
        $payload = json_encode(['uptime_seconds' => 120]);

        $signature = hash_hmac(
            'sha256',
            "{$timestamp}.{$nonce}.{$payload}",
            'super_secret_device_passphrase'
        );

        // 1. Success HMAC Authenticate
        $resolved = $authenticator->authenticate(
            $this->device->serial_number,
            $timestamp,
            $nonce,
            $signature,
            $payload
        );

        $this->assertEquals($this->device->id, $resolved->id);

        // 2. Replay attack block check
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Duplicate request nonce detected');

        $authenticator->authenticate(
            $this->device->serial_number,
            $timestamp,
            $nonce,
            $signature,
            $payload
        );
    }

    public function test_hmac_authentication_clock_skew(): void
    {
        $authenticator = app(HmacAuthenticator::class);
        $timestamp = (string) (time() - 400); // 6 mins skew (exceeds 5 mins limit)
        $nonce = Str::random(16);
        $payload = json_encode(['uptime_seconds' => 120]);

        $signature = hash_hmac(
            'sha256',
            "{$timestamp}.{$nonce}.{$payload}",
            'super_secret_device_passphrase'
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Request skew is too high');

        $authenticator->authenticate(
            $this->device->serial_number,
            $timestamp,
            $nonce,
            $signature,
            $payload
        );
    }

    public function test_device_registration_and_assignments_temporal(): void
    {
        $this->assertEquals(DeviceStatus::Active, $this->device->status);
        $this->assertStringContainsString('IOT-', $this->device->device_number);

        $userId = (string) Str::uuid();

        // 1. Assign device to Vehicle
        $this->service->assignDevice($this->device, Vehicle::class, $this->vehicle->id, $userId);

        $activeAssignment = DeviceAssignment::where('device_id', $this->device->id)
            ->whereNull('released_at')
            ->firstOrFail();

        $this->assertEquals(Vehicle::class, $activeAssignment->assignable_type);
        $this->assertEquals($this->vehicle->id, $activeAssignment->assignable_id);

        // 2. Reassign / Release device
        $this->service->releaseDevice($this->device, 'Vehicle decommissioned.', $userId);

        $activeAssignment->refresh();
        $this->assertNotNull($activeAssignment->released_at);
        $this->assertEquals('Vehicle decommissioned.', $activeAssignment->released_reason);

        // Assert number of assignments matches temporal log count (1 row intact)
        $this->assertEquals(1, DeviceAssignment::where('device_id', $this->device->id)->count());
    }

    public function test_telemetry_ingestion_updates_health_snapshots(): void
    {
        $processor = app(TelemetryProcessor::class);

        $telemetryData = [
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'speed_kph' => 45.5,
            'heading_degrees' => 180,
            'diagnostics' => [
                'cpu_usage_percent' => 35,
                'memory_usage_percent' => 48,
                'disk_usage_percent' => 12,
                'temperature_celsius' => 42,
            ],
        ];

        // Process telemetry logs
        $processor->process($this->device, $telemetryData);

        // Assert Telemetry Log is created
        $log = DeviceTelemetryLog::where('device_id', $this->device->id)->firstOrFail();
        $this->assertEquals(28.6139, $log->latitude);

        // Assert mutable health snapshot is updated
        $snapshot = DeviceHealthSnapshot::where('device_id', $this->device->id)->firstOrFail();
        $this->assertEquals(35, $snapshot->cpu_usage_percent);
        $this->assertEquals(42, $snapshot->temperature_celsius);
        $this->assertGreaterThan(0, $snapshot->overall_health_score);
    }

    public function test_offline_detection_scheduler_trigger(): void
    {
        // 1. Backdate last_seen_at to exceed 10 minutes limit
        $this->device->update(['last_seen_at' => now()->subMinutes(15)]);

        // 2. Trigger OfflineDetection scheduled job logic
        $this->service->detectOfflineDevices();

        $this->device->refresh();
        $this->assertEquals(DeviceStatus::Offline, $this->device->status);

        // Assert Device Alert was raised
        $alert = DeviceAlert::where('device_id', $this->device->id)->firstOrFail();
        $this->assertEquals('Device Offline', $alert->alert_type);
        $this->assertEquals('critical', $alert->severity);
    }

    public function test_command_queue_retries_and_idempotency(): void
    {
        $idempotencyKey = 'unique_idempotency_marker_key_101';

        // 1. Queue command
        $cmd1 = $this->service->queueCommand(
            $this->device,
            'Reboot',
            ['delay' => 10],
            null,
            $idempotencyKey
        );

        // 2. Queue again with same idempotency key returns first command
        $cmd2 = $this->service->queueCommand(
            $this->device,
            'Reboot',
            ['delay' => 10],
            null,
            $idempotencyKey
        );

        $this->assertEquals($cmd1->id, $cmd2->id);
    }

    public function test_telemetry_cascades_to_vehicle_gps_logs(): void
    {
        $processor = app(TelemetryProcessor::class);
        $userId = (string) Str::uuid();

        // 1. Assign device to Vehicle
        $this->service->assignDevice($this->device, Vehicle::class, $this->vehicle->id, $userId);

        $telemetryData = [
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'speed_kph' => 60.0,
            'heading_degrees' => 90,
            'diagnostics' => [],
        ];

        // 2. Process telemetry logs
        $processor->process($this->device, $telemetryData);

        // 3. Assert VehicleGPSLog record was cascaded
        $gpsLog = VehicleGPSLog::where('vehicle_id', $this->vehicle->id)->firstOrFail();
        $this->assertEquals(12.9716, $gpsLog->latitude);
        $this->assertEquals(77.5946, $gpsLog->longitude);
        $this->assertEquals(60.0, $gpsLog->speed_kmh);
    }
}
