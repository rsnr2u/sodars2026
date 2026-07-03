<?php

declare(strict_types=1);

namespace App\Modules\Finance\Infrastructure\Repositories;

use App\Modules\Finance\Application\DTOs\InvoiceFilterData;
use App\Modules\Finance\Domain\Entities\Invoice;
use App\Modules\Finance\Domain\Repositories\InvoiceReadRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceReadRepository implements InvoiceReadRepositoryInterface
{
    public function findById(string $id): ?Invoice
    {
        return Invoice::find($id);
    }

    public function findOrFail(string $id): Invoice
    {
        return Invoice::findOrFail($id);
    }

    public function findByNumber(string $number): ?Invoice
    {
        return Invoice::where('invoice_number', $number)->first();
    }

    /**
     * @return LengthAwarePaginator<Invoice>
     */
    public function paginate(InvoiceFilterData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Invoice::query()->with(['customer', 'branch']);

        if ($filters->status) {
            $query->where('status', $filters->status);
        }

        if ($filters->invoiceType) {
            $query->where('invoice_type', $filters->invoiceType);
        }

        if ($filters->customerId) {
            $query->where('customer_id', $filters->customerId);
        }

        if ($filters->branchId) {
            $query->where('branch_id', $filters->branchId);
        }

        if ($filters->search) {
            $query->where('invoice_number', 'like', '%' . $filters->search . '%');
        }

        return $query->latest()->paginate($perPage);
    }
}
