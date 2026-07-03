<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Wallet\Domain\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends BaseModel
{
    protected $table = 'withdrawals';

    protected $fillable = [
        'id',
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
