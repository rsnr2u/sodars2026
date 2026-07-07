<?php

declare(strict_types=1);

namespace App\Platform\Scheduler\Application\Jobs;

use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Platform\Scheduler\Application\Services\SchedulerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessScheduledJobsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(SchedulerService $scheduler): void
    {
        $overdue = ScheduledJob::where('status', 'pending')
            ->where('execute_at', '<=', now())
            ->get();

        foreach ($overdue as $job) {
            $scheduler->executeJob($job);
        }
    }
}
