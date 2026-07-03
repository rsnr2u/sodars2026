<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\Queries;

use App\Modules\Finance\Application\DTOs\InvoiceFilterData;
use App\Modules\Finance\Domain\Repositories\InvoiceReadRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ListInvoicesQuery
{
    public function __construct(protected InvoiceReadRepositoryInterface $readRepo) {}

    /**
     * @return LengthAwarePaginator<\App\Modules\Finance\Domain\Entities\Invoice>
     */
    public function execute(InvoiceFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->readRepo->paginate($filters, $perPage);
    }
}
