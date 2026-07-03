<?php

declare(strict_types=1);

namespace App\Modules\CRM\Application\Queries;

use App\Modules\CRM\Domain\Entities\Quotation;

class GetQuotationDetailsQuery
{
    public function execute(string $id): Quotation
    {
        return Quotation::with(['account', 'activeVersion.items.face', 'opportunity'])
            ->findOrFail($id);
    }
}
