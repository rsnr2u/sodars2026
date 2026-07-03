<?php

declare(strict_types=1);

namespace App\Platform\Identifiers;

class InvoiceNumberGenerator
{
    /**
     * Generate sequential code (INV-YYYY-XXXXXX).
     */
    public function generate(): string
    {
        $year = date('Y');
        
        // We will query global documents or payments to determine sequence count in v1
        $count = \Illuminate\Support\Facades\DB::table('invoices')
            ->whereYear('created_at', (int) $year)
            ->count();

        $nextSeq = $count + 1;
        return 'INV-' . $year . '-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
    }
}
