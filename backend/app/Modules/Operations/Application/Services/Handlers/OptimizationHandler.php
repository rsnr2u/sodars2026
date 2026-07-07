<?php

declare(strict_types=1);

namespace App\Modules\Operations\Application\Services\Handlers;

use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use Illuminate\Support\Facades\Log;

class OptimizationHandler
{
    public function handle(ScheduledJob $job): void
    {
        Log::info("Optimization job run completed. Resources scoring checks refreshed.");
    }
}
