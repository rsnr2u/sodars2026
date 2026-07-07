<?php

declare(strict_types=1);

namespace App\Platform\Scheduler\Infrastructure\Retry;

use App\Platform\Scheduler\Domain\Contracts\RetryStrategy;
use Carbon\Carbon;

class FixedRetryStrategy implements RetryStrategy
{
    public function nextExecution(array $policy, int $attempts): Carbon
    {
        $delay = (int) ($policy['initial_delay'] ?? 60);
        return Carbon::now()->addSeconds($delay);
    }
}
