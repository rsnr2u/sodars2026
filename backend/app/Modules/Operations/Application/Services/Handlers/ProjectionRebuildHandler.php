<?php

declare(strict_types=1);

namespace App\Modules\Operations\Application\Services\Handlers;

use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ProjectionRebuildHandler
{
    public function handle(ScheduledJob $job): void
    {
        Log::info("Operations projections rebuild run complete.");
    }
}
