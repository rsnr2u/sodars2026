<?php

declare(strict_types=1);

namespace App\Modules\CRM\Application\Actions;

use App\Modules\CRM\Domain\Entities\Opportunity;
use App\Modules\CRM\Domain\Services\CrmService;

class CreateOpportunityAction
{
    public function __construct(protected CrmService $crmService) {}

    public function execute(array $data): Opportunity
    {
        return $this->crmService->createOpportunity($data);
    }
}
