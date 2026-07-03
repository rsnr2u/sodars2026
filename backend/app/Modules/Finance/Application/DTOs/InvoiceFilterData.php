<?php

declare(strict_types=1);

namespace App\Modules\Finance\Application\DTOs;

use Illuminate\Http\Request;

class InvoiceFilterData
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?string $invoiceType = null,
        public readonly ?string $customerId = null,
        public readonly ?string $branchId = null,
        public readonly ?string $search = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            status: $request->query('status'),
            invoiceType: $request->query('invoice_type'),
            customerId: $request->query('customer_id'),
            branchId: $request->query('branch_id'),
            search: $request->query('search')
        );
    }
}
