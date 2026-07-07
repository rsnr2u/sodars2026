<?php

declare(strict_types=1);

namespace App\Modules\IoT\Domain\Events;

use App\Core\Events\BusinessEvent;
use App\Modules\IoT\Domain\Entities\Device;

abstract class AbstractDeviceEvent extends BusinessEvent
{
    public function getEntityClass(): string
    {
        return Device::class;
    }
}
