<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Domain\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Cheque = 'cheque';
    case Upi = 'upi';
    case Neft = 'neft';
    case Rtgs = 'rtgs';
}
