<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Queries;

use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Repositories\InvoiceReadRepositoryInterface;

class GetInvoiceDetailsQuery
{
    public function __construct(protected InvoiceReadRepositoryInterface $readRepo) {}

    public function execute(string $id): Invoice
    {
        return $this->readRepo->findOrFail($id);
    }
}
