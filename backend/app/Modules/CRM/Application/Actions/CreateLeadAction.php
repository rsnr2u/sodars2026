<?php

declare(strict_types=1);

namespace App\Modules\CRM\Application\Actions;

use App\Modules\CRM\Domain\Entities\Lead;
use App\Modules\CRM\Domain\Services\CrmService;

class CreateLeadAction
{
    public function __construct(protected CrmService $crmService) {}

    public function execute(array $data): Lead
    {
        return $this->crmService->createLead($data);
    }
}
