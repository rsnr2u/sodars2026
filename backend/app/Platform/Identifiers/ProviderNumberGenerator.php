<?php

declare(strict_types=1);

namespace App\Platform\Identifiers;

use Illuminate\Support\Facades\DB;

class ProviderNumberGenerator
{
    /**
     * Generate sequential code (PRV-TEST-001 or standard PRV-000001).
     */
    public function generate(): string
    {
        $count = DB::table('providers')->count();
        $nextSeq = $count + 1;
        return 'PRV-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
    }
}
