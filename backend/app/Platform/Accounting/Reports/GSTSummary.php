<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Reports;

class GSTSummary
{
    public function generate(): array
    {
        return ['input_gst' => 0, 'output_gst' => 0, 'net_payable' => 0];
    }
}
