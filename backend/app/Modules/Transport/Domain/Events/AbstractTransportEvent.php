<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Events;

use App\Core\Events\BusinessEvent;
use App\Modules\Transport\Domain\Entities\Vehicle;

abstract class AbstractTransportEvent extends BusinessEvent
{
    public function getEntityClass(): string
    {
        return Vehicle::class;
    }
}
