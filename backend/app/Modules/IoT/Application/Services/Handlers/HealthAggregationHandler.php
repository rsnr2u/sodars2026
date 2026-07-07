<?php

declare(strict_types=1);

namespace App\Modules\IoT\Application\Services\Handlers;

use App\Platform\Scheduler\Application\Services\ScheduledJobHandler;
use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Modules\IoT\Domain\Entities\DeviceHealthSnapshot;

class HealthAggregationHandler implements ScheduledJobHandler
{
    public function handle(ScheduledJob $job): void
    {
        // Re-evaluates health snapshots summary states
        $snapshots = DeviceHealthSnapshot::all();
        foreach ($snapshots as $snapshot) {
            $snapshot->touch();
        }
    }
}
