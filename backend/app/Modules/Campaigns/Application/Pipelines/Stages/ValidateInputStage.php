<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Pipelines\Stages;

use Closure;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ValidateInputStage
{
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];

        $start = Carbon::parse($dto->startDate);
        $end = Carbon::parse($dto->endDate);

        if ($start->isPast() && !$start->isToday()) {
            throw ValidationException::withMessages([
                'start_date' => ['Campaign start date cannot be in the past.'],
            ]);
        }

        if ($end->lessThan($start)) {
            throw ValidationException::withMessages([
                'end_date' => ['Campaign end date must be after or equal to start date.'],
            ]);
        }

        return $next($passable);
    }
}
