<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Enums;

enum OwnershipType: string
{
    case Owned = 'owned';
    case Leased = 'leased';
    case Partner = 'partner';
    case Government = 'government';
    case ThirdParty = 'third_party';
}
