<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Reports;

class CustomerStatement
{
    public function generate(string $customerId): array
    {
        return ['customer_id' => $customerId, 'total_invoiced' => 0, 'total_paid' => 0, 'outstanding' => 0];
    }
}
