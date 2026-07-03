<?php

declare(strict_types=1);

namespace App\Modules\CRM\Application\Actions;

use App\Modules\CRM\Domain\Entities\Lead;
use App\Modules\CRM\Domain\Services\CrmService;

class QualifyLeadAction
{
    public function __construct(protected CrmService $crmService) {}

    public function execute(string $leadId): Lead
    {
        return $this->crmService->qualifyLead($leadId);
    }
}
