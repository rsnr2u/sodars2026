<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Services;

use App\Modules\CRM\Domain\Entities\Lead;

class LeadScoreCalculator
{
    public function __construct(protected LeadScoreStrategy $strategy) {}

    public function calculate(Lead $lead): int
    {
        return $this->strategy->calculate($lead);
    }
}
