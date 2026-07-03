<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSettlementActivity extends Model
{
    use HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'provider_settlement_activities';

    protected $fillable = [
        'id',
        'provider_settlement_id',
        'performed_by',
        'action',
        'description',
        'trace_id',
    ];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(ProviderSettlement::class, 'provider_settlement_id');
    }
}
