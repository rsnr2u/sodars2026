<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Services;

use App\Modules\CRM\Domain\Entities\Lead;

interface LeadScoreStrategy
{
    /**
     * Compute lead score (0-100).
     */
    public function calculate(Lead $lead): int;
}
