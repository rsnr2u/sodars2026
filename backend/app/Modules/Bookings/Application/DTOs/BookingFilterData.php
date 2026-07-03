<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\DTOs;

use Illuminate\Http\Request;

class BookingFilterData
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?string $customerId = null,
        public readonly ?string $branchId = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $search = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            status: $request->query('status'),
            customerId: $request->query('customer_id'),
            branchId: $request->query('branch_id'),
            startDate: $request->query('start_date'),
            endDate: $request->query('end_date'),
            search: $request->query('search')
        );
    }
}
