<?php

declare(strict_types=1);

namespace App\Platform\Accounting\ChartOfAccounts;

use App\Core\Models\BaseBusinessModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends BaseBusinessModel
{
    protected $table = 'ledger_accounts';

    protected $fillable = [
        'organization_id',
        'id',
        'parent_account_id',
        'name',
        'code',
        'type',
        'normal_balance',
        'is_control_account',
        'allow_manual_posting',
        'is_active',
        'currency',
    ];

    protected $casts = [
        'is_control_account' => 'boolean',
        'allow_manual_posting' => 'boolean',
        'is_active' => 'boolean',
        'type' => AccountType::class,
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'parent_account_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(LedgerAccount::class, 'parent_account_id');
    }
}
