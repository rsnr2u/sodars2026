<?php

declare(strict_types=1);

namespace App\Platform\Identifiers;

use Illuminate\Support\Facades\DB;

class BookingNumberGenerator
{
    /**
     * Generate sequential code (BK-YYYY-XXXXXX).
     */
    public function generate(): string
    {
        $year = date('Y');
        
        // Fetch max count or sequence for booking in the current year
        $count = DB::table('bookings')
            ->whereYear('created_at', (int) $year)
            ->count();

        $nextSeq = $count + 1;
        return 'BK-' . $year . '-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
    }
}
