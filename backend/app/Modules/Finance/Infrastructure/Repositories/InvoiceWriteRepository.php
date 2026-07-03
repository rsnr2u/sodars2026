<?php

declare(strict_types=1);

namespace App\Modules\Finance\Infrastructure\Repositories;

use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Repositories\InvoiceWriteRepositoryInterface;

class InvoiceWriteRepository implements InvoiceWriteRepositoryInterface
{
    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function update(string $id, array $data): Invoice
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update($data);
        return $invoice;
    }

    public function delete(string $id): bool
    {
        $invoice = Invoice::findOrFail($id);
        return $invoice->delete();
    }
}
