<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Journal;

use App\Core\Models\BaseModel;
use App\Platform\Accounting\ChartOfAccounts\LedgerAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LedgerEntry extends BaseModel
{
    protected $table = 'ledger_entries';

    protected $fillable = [
        'id',
        'journal_id',
        'ledger_account_id',
        'line_number',
        'entry_type',
        'amount_cents',
        'description',
        
        // Multi-currency details
        'base_currency',
        'exchange_rate',
        'base_amount_cents',

        // Polymorphic origin references
        'ledgerable_type',
        'ledgerable_id',
    ];

    protected $casts = [
        'line_number' => 'integer',
        'amount_cents' => 'integer',
        'exchange_rate' => 'float',
        'base_amount_cents' => 'integer',
        'entry_type' => EntryType::class,
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(LedgerJournal::class, 'journal_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_account_id');
    }

    public function ledgerable(): MorphTo
    {
        return $this->morphTo();
    }
}
