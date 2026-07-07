<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\Wallet\Domain\Enums\WalletState;
use App\Platform\Accounting\ChartOfAccounts\LedgerAccount;
use App\Platform\Search\Domain\Contracts\Searchable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Wallet extends BaseBusinessModel implements Searchable
{
    protected $table = 'wallets';

    protected $fillable = [
        'id',
        'organization_id',
        'wallet_number',
        'holder_type',
        'holder_id',
        'ledger_account_id',
        'wallet_type', // provider, customer, branch, corporate, escrow, system
        'currency',
        'status', // active, suspended, closed, frozen, draft
    ];

    protected $casts = [
        'status' => WalletState::class,
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

    public function getBalanceAttribute(): int
    {
        return app(\App\Modules\Wallet\Domain\Services\WalletService::class)->calculateDynamicBalance($this->id);
    }

    // ─── Searchable Implementation ────────────────────────────────

    public function toSearchDocument(): array
    {
        return [
            'searchable_text' => implode(' ', array_filter([
                $this->wallet_number,
                $this->wallet_type,
                $this->currency,
            ])),
            'filterable_attributes' => [
                'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
                'organization_id' => $this->organization_id,
            ],
            'facet_values' => [
                'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            ],
            'sortable_attributes' => [
                'created_at' => $this->created_at?->toIso8601String(),
            ],
            'display_data' => [
                'wallet_number' => $this->wallet_number,
                'type' => $this->wallet_type,
                'status' => $this->status instanceof \BackedEnum ? $this->status->value : $this->status,
            ],
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'wallet_wallets';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'wallet_number' => 'string',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status'];
    }
}
