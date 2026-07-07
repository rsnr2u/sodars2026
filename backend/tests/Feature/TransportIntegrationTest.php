<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Transport\Domain\Entities\Vehicle;
use App\Modules\Transport\Domain\Entities\Driver;
use App\Modules\Transport\Domain\Entities\Route as TransportRoute;
use App\Modules\Transport\Domain\Entities\VehicleGPSLog;
use App\Modules\Transport\Domain\Enums\VehicleStatus;
use App\Modules\Transport\Domain\Enums\DriverStatus;
use App\Modules\Transport\Domain\Enums\RouteStatus;
use App\Modules\Transport\Application\Services\TransportService;
use App\Platform\Identity\Domain\Entities\Organization;
use App\Platform\Identity\Application\Services\IdentityContext;
use App\Platform\Search\Application\Jobs\RebuildIndexJob;
use App\Platform\Search\Application\Services\SearchService;
use App\Platform\Reporting\Domain\ValueObjects\ReportParameters;
use App\Platform\Reporting\Infrastructure\Reports\FleetUtilizationReport;
use App\Platform\Reporting\Infrastructure\Reports\FleetMaintenanceReport;
use App\Platform\Reporting\Infrastructure\Reports\FuelEfficiencyReport;
use App\Platform\Reporting\Infrastructure\Reports\RouteAnalysisReport;
use App\Platform\Reporting\Infrastructure\Reports\VehicleDowntimeReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Core\ApiTestCase;

class TransportIntegrationTest extends ApiTestCase
{
    use RefreshDatabase;

    protected TransportService $transportService;
    protected string $tenantAOrgId;
    protected string $tenantBOrgId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->transportService = app(TransportService::class);

        // Setup tenants
        $orgA = Organization::create([
            'name' => 'Tenant A Fleet Co',
            'slug' => 'tenant-a-fleet',
            'domain' => 'tenant-a-fleet.com',
            'is_active' => true,
        ]);
        $this->tenantAOrgId = $orgA->id;

        $orgB = Organization::create([
            'name' => 'Tenant B Fleet Co',
            'slug' => 'tenant-b-fleet',
            'domain' => 'tenant-b-fleet.com',
            'is_active' => true,
        ]);
        $this->tenantBOrgId = $orgB->id;
    }

    public function test_vehicle_and_driver_lifecycle_workflows(): void
    {
        $admin = $this->actingAsAdmin();

        // 1. Create Vehicle under Tenant A context
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);

        $vehicle = $this->transportService->createVehicle([
            'license_plate' => 'KA-01-ME-1234',
            'make' => 'Tata',
            'model' => 'Ultra 1918',
            'year' => 2024,
            'current_odometer' => 12000,
            'payload_capacity' => 10500.50,
            'volume_capacity' => 45.80,
            'number_of_screens' => 2,
            'max_billboards' => 1,
            'vehicle_health_score' => 98.5,
        ]);

        $this->assertNotNull($vehicle->id);
        $this->assertEquals('VEH-000001', $vehicle->vehicle_number);
        $this->assertEquals(VehicleStatus::Active, $vehicle->status);

        // 2. Create Driver under Tenant A
        $driver = $this->transportService->createDriver([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'license_number' => 'DL-987654321',
            'license_class' => 'Heavy HGV',
            'license_expiry' => '2028-12-31',
            'medical_expiry' => '2025-06-30',
            'badge_number' => 'BDG-9988',
            'joining_date' => '2024-01-15',
            'status' => 'active',
        ]);

        $this->assertNotNull($driver->id);
        $this->assertEquals('DRV-000001', $driver->driver_number);

        // 3. Assign Driver to Vehicle
        $assignment = $this->transportService->assignDriver($vehicle->id, $driver->id, 'Assigned for inter-state transit.');
        $this->assertNotNull($assignment->id);
        $this->assertEquals($vehicle->id, $assignment->vehicle_id);
        $this->assertEquals($driver->id, $assignment->driver_id);
        $this->assertNull($assignment->assigned_to);

        // 4. Log Fuel purchase
        $fuel = $this->transportService->logFuel($vehicle->id, [
            'fuel_date' => '2026-07-05',
            'liters' => 120.50,
            'cost_cents' => 11000,
            'odometer_reading' => 12150,
            'fuel_station' => 'Shell Techpark',
            'payment_method' => 'card',
        ]);
        $this->assertNotNull($fuel->id);
        $this->assertEquals(12150, $vehicle->refresh()->current_odometer);

        // 5. Log Maintenance
        $maintenance = $this->transportService->logMaintenance($vehicle->id, [
            'maintenance_type' => 'routine',
            'description' => 'Engine oil replacement and filter check.',
            'cost_cents' => 35000,
            'maintenance_date' => '2026-07-05',
            'odometer_reading' => 12150,
            'status' => 'Completed',
            'next_due_date' => '2026-10-05',
            'next_due_odometer' => 17150,
        ]);
        $this->assertNotNull($maintenance->id);
        $this->assertEquals('Completed', $maintenance->status);

        // 6. Log Decoupled GPS Telemetry Log
        $gps = $this->transportService->logGPS($vehicle->id, [
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'speed_kmh' => 65.50,
            'heading' => 180.00,
            'engine_status' => 'on',
            'ignition_status' => 'on',
        ]);
        $this->assertNotNull($gps->id);
        $this->assertEquals(12.9716, $gps->latitude);

        // Make sure loading vehicle aggregate does not load telemetry relations (kept decoupled)
        $vehicleReloaded = Vehicle::with('maintenances', 'fuelLogs', 'assignments')->find($vehicle->id);
        $this->assertFalse(array_key_exists('gpsLogs', $vehicleReloaded->getRelations()));
    }

    public function test_route_state_transitions(): void
    {
        $admin = $this->actingAsAdmin();
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);

        // Create initial vehicle & driver
        $vehicle = $this->transportService->createVehicle([
            'license_plate' => 'KA-01-ME-1234', 'make' => 'Tata', 'model' => 'Ultra', 'year' => 2024
        ]);
        $driver = $this->transportService->createDriver([
            'first_name' => 'John', 'last_name' => 'Doe', 'license_number' => 'DL-1', 'license_class' => 'A', 'license_expiry' => '2028-12-31', 'joining_date' => '2024-01-15'
        ]);

        // 1. Create Route in Draft state
        $route = $this->transportService->createRoute([
            'start_location' => 'HQ Bengaluru',
            'end_location' => 'Hubballi Yard',
            'planned_distance_km' => 410.50,
            'planned_duration_minutes' => 480,
            'status' => 'Draft',
        ]);

        $this->assertEquals('ROT-000001', $route->route_number);
        $this->assertEquals(RouteStatus::Draft, $route->status);

        // 2. Assign Vehicle and Driver (transitions to Assigned)
        $route = $this->transportService->assignRoute($route->id, $vehicle->id, $driver->id);
        $this->assertEquals(RouteStatus::Assigned, $route->status);
        $this->assertEquals($vehicle->id, $route->vehicle_id);

        // 3. Dispatch Route (transitions to Dispatched, records started_at)
        $route = $this->transportService->dispatchRoute($route->id);
        $this->assertEquals(RouteStatus::Dispatched, $route->status);
        $this->assertNotNull($route->started_at);

        // 4. Change status to In Transit
        $route = $this->transportService->changeRouteStatus($route->id, RouteStatus::InTransit);
        $this->assertEquals(RouteStatus::InTransit, $route->status);

        // 5. Complete Route
        $route = $this->transportService->changeRouteStatus($route->id, RouteStatus::Completed, [
            'actual_distance_km' => 415.20,
            'actual_duration_minutes' => 495,
        ]);
        $this->assertEquals(RouteStatus::Completed, $route->status);
        $this->assertEquals(415.20, $route->actual_distance_km);
        $this->assertNotNull($route->completed_at);
    }

    public function test_multi_tenant_isolation_enforced(): void
    {
        $admin = $this->actingAsAdmin();

        // Setup Tenant A vehicle
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);
        $vehicleA = $this->transportService->createVehicle([
            'license_plate' => 'KA-01-ME-1111', 'make' => 'Tata', 'model' => 'Ultra', 'year' => 2024
        ]);

        // Setup Tenant B vehicle
        IdentityContext::setContext($admin->id, $this->tenantBOrgId, null);
        $vehicleB = $this->transportService->createVehicle([
            'license_plate' => 'KA-01-ME-2222', 'make' => 'Eicher', 'model' => 'Pro 3019', 'year' => 2025
        ]);

        // Verify Tenant A context query
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);
        $vehiclesA = Vehicle::all();
        $this->assertCount(1, $vehiclesA);
        $this->assertEquals($vehicleA->id, $vehiclesA->first()->id);

        // Verify Tenant B context query
        IdentityContext::setContext($admin->id, $this->tenantBOrgId, null);
        $vehiclesB = Vehicle::all();
        $this->assertCount(1, $vehiclesB);
        $this->assertEquals($vehicleB->id, $vehiclesB->first()->id);
    }

    public function test_search_index_mapping(): void
    {
        $admin = $this->actingAsAdmin();
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);

        // Register event listener check
        $vehicle = $this->transportService->createVehicle([
            'license_plate' => 'KA-01-ME-1111',
            'make' => 'Tata',
            'model' => 'Ultra',
            'year' => 2024,
        ]);

        // Force rebuild search index job to update database records
        RebuildIndexJob::dispatchSync('transport_vehicles');

        $searchService = app(SearchService::class);
        $results = $searchService->search('transport_vehicles', \App\Platform\Search\Domain\ValueObjects\SearchQuery::create('Tata'));

        $this->assertGreaterThanOrEqual(1, $results->total);
    }

    public function test_reports_are_tenant_scoped(): void
    {
        $admin = $this->actingAsAdmin();

        // 1. Setup Tenant A data
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);
        $vehicleA = $this->transportService->createVehicle([
            'license_plate' => 'KA-01-ME-1111', 'make' => 'Tata', 'model' => 'Ultra', 'year' => 2024, 'status' => 'active'
        ]);
        $this->transportService->logFuel($vehicleA->id, [
            'fuel_date' => '2026-07-05', 'liters' => 50, 'cost_cents' => 4500, 'odometer_reading' => 12050
        ]);
        $this->transportService->logMaintenance($vehicleA->id, [
            'maintenance_type' => 'routine', 'cost_cents' => 15000, 'maintenance_date' => '2026-07-05', 'odometer_reading' => 12050, 'status' => 'Completed'
        ]);
        $this->transportService->createRoute([
            'start_location' => 'HQ A', 'end_location' => 'Hub A', 'planned_distance_km' => 100, 'planned_duration_minutes' => 120, 'status' => 'Completed'
        ]);

        // 2. Setup Tenant B data
        IdentityContext::setContext($admin->id, $this->tenantBOrgId, null);
        $vehicleB = $this->transportService->createVehicle([
            'license_plate' => 'KA-01-ME-2222', 'make' => 'Eicher', 'model' => 'Pro 3019', 'year' => 2025, 'status' => 'maintenance'
        ]);
        $this->transportService->logFuel($vehicleB->id, [
            'fuel_date' => '2026-07-05', 'liters' => 100, 'cost_cents' => 9000, 'odometer_reading' => 5050
        ]);
        $this->transportService->logMaintenance($vehicleB->id, [
            'maintenance_type' => 'repair', 'cost_cents' => 50000, 'maintenance_date' => '2026-07-05', 'odometer_reading' => 5050, 'status' => 'Completed'
        ]);

        // 3. Verify Tenant A Reports Scoping
        IdentityContext::setContext($admin->id, $this->tenantAOrgId, null);

        // Fleet Utilization Report
        $utilReport = app(FleetUtilizationReport::class);
        $dataUtil = $utilReport->generate(new ReportParameters([]));
        $this->assertEquals(1, $dataUtil['summary']['total_vehicles']);
        $this->assertEquals(1, $dataUtil['summary']['active_vehicles']);

        // Fleet Maintenance Report
        $maintReport = app(FleetMaintenanceReport::class);
        $dataMaint = $maintReport->generate(new ReportParameters([]));
        $this->assertEquals(1, $dataMaint['summary']['total_records']);
        $this->assertEquals(15000, $dataMaint['summary']['total_cost_cents']);

        // Fuel Efficiency Report
        $fuelReport = app(FuelEfficiencyReport::class);
        $dataFuel = $fuelReport->generate(new ReportParameters([]));
        $this->assertEquals(1, $dataFuel['summary']['total_refills']);
        $this->assertEquals(50, $dataFuel['summary']['total_liters']);

        // Route Analysis Report
        $routeReport = app(RouteAnalysisReport::class);
        $dataRoute = $routeReport->generate(new ReportParameters([]));
        $this->assertEquals(1, $dataRoute['summary']['total_routes']);

        // Vehicle Downtime Report
        $downtimeReport = app(VehicleDowntimeReport::class);
        $dataDowntime = $downtimeReport->generate(new ReportParameters([]));
        $this->assertEquals(0, $dataDowntime['summary']['total_downtime_vehicles']);
    }
}
