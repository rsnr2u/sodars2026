<?php

declare(strict_types=1);

namespace App\Platform\Identifiers;

use Illuminate\Support\Facades\DB;

class DeviceNumberGenerator
{
    /**
     * Generate sequential code (IOT-000001).
     */
    public function generate(): string
    {
        $count = DB::table('devices')->count();
        $nextSeq = $count + 1;
        return 'IOT-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
    }
}
