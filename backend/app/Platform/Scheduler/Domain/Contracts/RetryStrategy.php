<?php

declare(strict_types=1);

namespace App\Platform\Scheduler\Domain\Contracts;

use Carbon\Carbon;

interface RetryStrategy
{
    /**
     * Compute the next execution date based on retry policy and current attempt count.
     */
    public function nextExecution(array $policy, int $attempts): Carbon;
}
