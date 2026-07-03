<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Repositories;

use App\Modules\Finance\Domain\Entities\Invoice;

interface InvoiceWriteRepositoryInterface
{
    public function create(array $data): Invoice;

    public function update(string $id, array $data): Invoice;

    public function delete(string $id): bool;
}
