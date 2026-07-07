<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Settlement = 'settlement';
    case Transfer = 'transfer';
    case Commission = 'commission';
    case Adjustment = 'adjustment';
    case Refund = 'refund';
    case Reversal = 'reversal';
    case Fee = 'fee';
}
