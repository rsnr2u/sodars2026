<?php

declare(strict_types=1);

namespace App\Platform\Scheduler\Application\Services;

use App\Platform\Scheduler\Domain\Entities\ScheduledJob;

interface ScheduledJobHandler
{
    /**
     * Handle the execution of the scheduled job payload.
     */
    public function handle(ScheduledJob $job): void;
}
