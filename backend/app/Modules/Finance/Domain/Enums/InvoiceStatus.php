<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Paid = 'paid';
    case PartiallyPaid = 'partially_paid';
    case Voided = 'voided';
    case Overdue = 'overdue';
}
