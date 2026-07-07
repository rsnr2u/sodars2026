<?php

declare(strict_types=1);

namespace App\Modules\Operations\Domain\Events;

use App\Core\Events\BusinessEvent;
use App\Modules\Operations\Domain\Entities\Schedule;

abstract class AbstractSchedulingEvent extends BusinessEvent
{
    public function getEntityClass(): string
    {
        return Schedule::class;
    }
}
