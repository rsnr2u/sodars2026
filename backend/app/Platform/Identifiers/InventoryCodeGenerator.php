<?php

declare(strict_types=1);

namespace App\Platform\Identifiers;

use Illuminate\Support\Facades\DB;

class InventoryCodeGenerator
{
    /**
     * Generate sequential code (INV-000001).
     */
    public function generate(): string
    {
        $count = DB::table('inventories')->count();
        $nextSeq = $count + 1;
        return 'INV-' . str_pad((string)$nextSeq, 6, '0', STR_PAD_LEFT);
    }
}
