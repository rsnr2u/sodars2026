<?php

declare(strict_types=1);

namespace App\Modules\CRM\Application\Actions;

use App\Modules\CRM\Domain\Entities\Quotation;
use App\Modules\CRM\Domain\Services\CrmService;

class CreateQuotationAction
{
    public function __construct(protected CrmService $crmService) {}

    public function execute(array $data): Quotation
    {
        return $this->crmService->createQuotation($data);
    }
}
