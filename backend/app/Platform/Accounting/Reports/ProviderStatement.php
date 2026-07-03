<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Reports;

class ProviderStatement
{
    public function generate(string $providerId): array
    {
        return ['provider_id' => $providerId, 'gross_earnings' => 0, 'tds_deducted' => 0, 'net_paid' => 0];
    }
}
