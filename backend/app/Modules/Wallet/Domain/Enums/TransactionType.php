<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Transfer = 'transfer';
    case Settlement = 'settlement';
    case Refund = 'refund';
    case Adjustment = 'adjustment';
    case Commission = 'commission';
    case Reversal = 'reversal';
    case Correction = 'correction';
}
