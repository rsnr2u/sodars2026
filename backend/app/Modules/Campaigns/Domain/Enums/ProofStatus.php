<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Enums;

enum ProofStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
}
