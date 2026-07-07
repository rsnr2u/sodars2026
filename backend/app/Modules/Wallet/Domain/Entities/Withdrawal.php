<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\Wallet\Domain\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends BaseBusinessModel
{
    protected $table = 'withdrawals';

    protected $fillable = [
        'id',
        'organization_id',
        'withdrawal_number',
        'wallet_id',
        'amount_cents',
        'bank_account_details',
        'status',
        'payout_reference',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'bank_account_details' => 'array',
        'status' => WithdrawalStatus::class,
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}
