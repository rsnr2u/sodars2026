<?php

declare(strict_types=1);

namespace App\Platform\Accounting\Journal;

use App\Core\Models\BaseModel;
use App\Platform\Accounting\ChartOfAccounts\AccountingPeriod;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerJournal extends BaseModel
{
    protected $table = 'ledger_journals';

    protected $fillable = [
        'id',
        'reference_number',
        'narration',
        'journal_type',
        'status',
        'reversal_of_journal_id',
        'accounting_period_id',
        
        // Metadata
        'source_module',
        'source_id',
        'source_type',
        'source_event',
        'tenant_id',
        'branch_id',
        'posted_by',
        'approved_by',
        
        // Trace context
        'trace_id',
        'correlation_id',
        'causation_id',

        'posted_at',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class, 'accounting_period_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'journal_id');
    }

    public function reversedJournal(): BelongsTo
    {
        return $this->belongsTo(LedgerJournal::class, 'reversal_of_journal_id');
    }
}
