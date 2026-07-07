<?php

declare(strict_types=1);

namespace App\Modules\Transport\Domain\Events;

class RouteDispatched extends AbstractTransportEvent
{
    public function getEntityClass(): string
    {
        return \App\Modules\Transport\Domain\Entities\Route::class;
    }
}
