<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Platform\Accounting\ChartOfAccounts\LedgerAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Wallet extends BaseModel
{
    protected $table = 'wallets';

    protected $fillable = [
        'id',
        'holder_type',
        'holder_id',
        'ledger_account_id',
        'wallet_type', // provider, customer, branch, corporate, escrow, system
        'currency',
        'status', // active, suspended
    ];

    public function holder(): MorphTo
    {
        return $this->morphTo();
    }

    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_account_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id');
    }
}
