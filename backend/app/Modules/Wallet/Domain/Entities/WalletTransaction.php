<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Platform\Accounting\Journal\LedgerJournal;
use App\Modules\Wallet\Domain\Enums\TransactionStatus;
use App\Modules\Wallet\Domain\Enums\TransactionType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends BaseModel
{
    protected $table = 'wallet_transactions';

    protected $fillable = [
        'id',
        'wallet_id',
        'ledger_journal_id',
        'amount_cents',
        'running_balance_snapshot',
        'type',
        'status',
        'reference_number',
        'metadata',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'running_balance_snapshot' => 'integer',
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
        'metadata' => 'array',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(LedgerJournal::class, 'ledger_journal_id');
    }
}
