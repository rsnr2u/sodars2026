<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Repositories;

use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Application\DTOs\InvoiceFilterData;
use Illuminate\Pagination\LengthAwarePaginator;

interface InvoiceReadRepositoryInterface
{
    public function findById(string $id): ?Invoice;

    public function findOrFail(string $id): Invoice;

    public function findByNumber(string $number): ?Invoice;

    /**
     * @return LengthAwarePaginator<Invoice>
     */
    public function paginate(InvoiceFilterData $filters, int $perPage = 15): LengthAwarePaginator;
}
