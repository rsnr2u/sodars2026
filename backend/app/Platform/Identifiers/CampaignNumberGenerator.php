<?php

declare(strict_types=1);

namespace App\Platform\Identifiers;

use Illuminate\Support\Facades\DB;

class CampaignNumberGenerator
{
    /**
     * Generate sequential code (CMP-000001).
     */
    public function generate(): string
    {
        $count = DB::table('campaigns')->count();
        $nextSeq = $count + 1;
        return 'CMP-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
    }
}
