<?php

declare(strict_types=1);

namespace App\Platform\Scheduler\Infrastructure\Retry;

use App\Platform\Scheduler\Domain\Contracts\RetryStrategy;
use Carbon\Carbon;

class ExponentialRetryStrategy implements RetryStrategy
{
    public function nextExecution(array $policy, int $attempts): Carbon
    {
        $delay = (int) ($policy['initial_delay'] ?? 60);
        $multiplier = (int) ($policy['multiplier'] ?? 2);
        $maxDelay = (int) ($policy['max_delay'] ?? 3600);

        $computedDelay = $delay * pow($multiplier, max(0, $attempts - 1));
        $finalDelay = min($computedDelay, $maxDelay);

        return Carbon::now()->addSeconds((int) $finalDelay);
    }
}
