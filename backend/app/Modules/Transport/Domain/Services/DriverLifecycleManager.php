<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Services;

use App\Modules\Transport\Domain\Entities\Driver;
use App\Modules\Transport\Domain\Enums\DriverStatus;

class DriverLifecycleManager
{
    public function createDriver(array $attributes): Driver
    {
        $driver = Driver::create($attributes);
        return $driver;
    }

    public function updateDriver(Driver $driver, array $attributes): void
    {
        $driver->update($attributes);
    }

    public function suspendDriver(Driver $driver): void
    {
        $driver->update(['status' => DriverStatus::Suspended]);
    }
}
