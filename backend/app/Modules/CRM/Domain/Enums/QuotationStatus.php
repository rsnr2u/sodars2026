<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Enums;

enum QuotationStatus: string
{
    case DRAFT = 'draft';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case EXPIRED = 'expired';
    case REJECTED = 'rejected';
}
