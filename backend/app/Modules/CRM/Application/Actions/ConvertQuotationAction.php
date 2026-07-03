<?php

declare(strict_types=1);

namespace App\Modules\CRM\Application\Actions;

use App\Modules\Bookings\Domain\Entities\Booking;
use App\Modules\CRM\Domain\Services\CrmService;

class ConvertQuotationAction
{
    public function __construct(protected CrmService $crmService) {}

    public function execute(string $quotationId, string $branchId, string $customerId): Booking
    {
        return $this->crmService->convertQuotation($quotationId, $branchId, $customerId);
    }
}
