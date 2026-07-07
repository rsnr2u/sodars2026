<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Core\ValueObjects\Money;
use App\Core\ValueObjects\Currency;
use App\Platform\Accounting\Contracts\FinancialDocument;
use App\Platform\Accounting\Journal\LedgerJournal;
use App\Modules\Wallet\Domain\Enums\TransactionStatus;
use App\Modules\Wallet\Domain\Enums\TransactionType;
use App\Modules\Wallet\Domain\Enums\PostingStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends BaseBusinessModel implements FinancialDocument
{
    protected $table = 'wallet_transactions';

    protected $fillable = [
        'id',
        'organization_id',
        'wallet_id',
        'ledger_journal_id',
        'amount_cents',
        'running_balance_snapshot',
        'type',
        'status', // maps to transaction status
        'posting_status',
        'reference_number',
        'transaction_reference',
        'sequence_number',
        'invoice_id',
        'payment_id',
        'settlement_id',
        'metadata',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'running_balance_snapshot' => 'integer',
        'sequence_number' => 'integer',
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
        'posting_status' => PostingStatus::class,
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function ($model) {
            throw new \DomainException("Wallet transactions are immutable and cannot be updated.");
        });

        static::deleting(function ($model) {
            throw new \DomainException("Wallet transactions are immutable and cannot be deleted.");
        });
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(LedgerJournal::class, 'ledger_journal_id');
    }

    // ─── FinancialDocument Interface Implementation ───────────────

    public function documentNumber(): string
    {
        return $this->transaction_reference ?? $this->id;
    }

    public function organizationId(): ?string
    {
        return $this->organization_id;
    }

    public function totalAmount(): Money
    {
        return new Money($this->amount_cents, new Currency($this->currency()));
    }

    public function currency(): string
    {
        return $this->wallet?->currency ?? 'INR';
    }

    public function postingReference(): string
    {
        return $this->transaction_reference ?? $this->reference_number;
    }
}
