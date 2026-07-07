<?php

declare(strict_types=1);

namespace App\Platform\Scheduler\Application\Services;

use App\Platform\Scheduler\Domain\Entities\ScheduledJob;
use App\Platform\Scheduler\Infrastructure\Retry\FixedRetryStrategy;
use App\Platform\Scheduler\Infrastructure\Retry\LinearRetryStrategy;
use App\Platform\Scheduler\Infrastructure\Retry\ExponentialRetryStrategy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SchedulerService
{
    public function __construct(
        protected SchedulerDispatcher $dispatcher
    ) {}

    /**
     * Schedule a new background platform job.
     */
    public function schedule(
        string $category,
        string $jobType,
        ?string $aggregateType,
        ?string $aggregateId,
        Carbon $executeAt,
        array $payload = [],
        array $retryPolicy = [],
        ?string $orgId = null
    ): ScheduledJob {
        return ScheduledJob::create([
            'id' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'category' => $category,
            'job_type' => $jobType,
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'execute_at' => $executeAt,
            'status' => 'pending',
            'payload' => $payload,
            'retry_policy' => $retryPolicy,
            'attempts' => 0,
        ]);
    }

    /**
     * Cancel pending scheduled jobs matching target properties.
     */
    public function cancel(string $aggregateType, string $aggregateId, ?string $jobType = null): void
    {
        $query = ScheduledJob::where('aggregate_type', $aggregateType)
            ->where('aggregate_id', $aggregateId)
            ->where('status', 'pending');

        if ($jobType) {
            $query->where('job_type', $jobType);
        }

        $query->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Execute a scheduled job, with retries and dead letter queuing.
     */
    public function executeJob(ScheduledJob $job): void
    {
        DB::transaction(function () use ($job) {
            $job->refresh();
            if ($job->status !== 'pending') {
                return;
            }

            $job->update([
                'status' => 'processing',
            ]);

            try {
                // Dispatch to specific job type handler
                $this->dispatcher->dispatch($job);

                $job->update([
                    'status' => 'completed',
                    'triggered_at' => now(),
                ]);
            } catch (Throwable $e) {
                $attempts = $job->attempts + 1;
                $maxAttempts = (int) ($job->retry_policy['max_attempts'] ?? 3);

                if ($attempts >= $maxAttempts) {
                    $job->update([
                        'status' => 'failed',
                        'attempts' => $attempts,
                        'last_error' => $e->getMessage() . "\n" . $e->getTraceAsString(),
                    ]);
                } else {
                    // Resolve retry strategy
                    $strategyName = $job->retry_policy['strategy'] ?? 'exponential';
                    $strategy = match ($strategyName) {
                        'fixed' => app(FixedRetryStrategy::class),
                        'linear' => app(LinearRetryStrategy::class),
                        default => app(ExponentialRetryStrategy::class),
                    };

                    $nextRun = $strategy->nextExecution($job->retry_policy, $attempts);

                    $job->update([
                        'status' => 'pending',
                        'attempts' => $attempts,
                        'execute_at' => $nextRun,
                        'last_error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}
