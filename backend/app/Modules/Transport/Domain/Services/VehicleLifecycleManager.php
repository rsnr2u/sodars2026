<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Services;

use App\Modules\Transport\Domain\Entities\Vehicle;
use App\Modules\Transport\Domain\Entities\VehicleMaintenance;
use App\Modules\Transport\Domain\Entities\VehicleFuelLog;
use App\Modules\Transport\Domain\Entities\VehicleAssignment;
use App\Modules\Transport\Domain\Enums\VehicleStatus;
use Carbon\Carbon;

class VehicleLifecycleManager
{
    public function createVehicle(array $attributes): Vehicle
    {
        $vehicle = Vehicle::create($attributes);
        return $vehicle;
    }

    public function updateVehicle(Vehicle $vehicle, array $attributes): void
    {
        $vehicle->update($attributes);
    }

    public function changeStatus(Vehicle $vehicle, VehicleStatus $status): void
    {
        $oldStatus = $vehicle->status;
        $vehicle->update(['status' => $status]);
    }

    public function logMaintenance(Vehicle $vehicle, array $data): VehicleMaintenance
    {
        $maintenance = $vehicle->maintenances()->create($data);
        return $maintenance;
    }

    public function logFuel(Vehicle $vehicle, array $data): VehicleFuelLog
    {
        $fuel = $vehicle->fuelLogs()->create($data);
        
        // Update current vehicle odometer if reading is higher
        if (($data['odometer_reading'] ?? 0) > $vehicle->current_odometer) {
            $vehicle->update(['current_odometer' => (int) $data['odometer_reading']]);
        }

        return $fuel;
    }

    public function assignDriver(Vehicle $vehicle, string $driverId, string $reason = null): VehicleAssignment
    {
        // Close any active assignments first
        $vehicle->assignments()
            ->whereNull('assigned_to')
            ->update(['assigned_to' => Carbon::now()]);

        $assignment = $vehicle->assignments()->create([
            'organization_id' => $vehicle->organization_id,
            'driver_id' => $driverId,
            'assigned_from' => Carbon::now(),
            'reason' => $reason,
        ]);

        return $assignment;
    }

    public function releaseDriver(Vehicle $vehicle): void
    {
        $vehicle->assignments()
            ->whereNull('assigned_to')
            ->update(['assigned_to' => Carbon::now()]);
    }
}
