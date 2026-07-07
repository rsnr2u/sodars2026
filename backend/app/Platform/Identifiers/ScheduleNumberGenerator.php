<?php

declare(strict_types=1);

namespace App\Platform\Identifiers;

use Illuminate\Support\Facades\DB;

class ScheduleNumberGenerator
{
    /**
     * Generate sequential code (SCH-000001).
     */
    public function generate(): string
    {
        $count = DB::table('operations_schedules')->count();
        $nextSeq = $count + 1;
        return 'SCH-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
    }
}
