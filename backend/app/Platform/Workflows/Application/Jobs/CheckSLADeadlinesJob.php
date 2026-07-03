<?php

declare(strict_types=1);

namespace App\Platform\Workflows\Application\Jobs;

use App\Platform\Workflows\Application\Services\WorkflowEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckSLADeadlinesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(WorkflowEngineService $service): void
    {
        $service->escalateOverdueTasks();
    }
}
