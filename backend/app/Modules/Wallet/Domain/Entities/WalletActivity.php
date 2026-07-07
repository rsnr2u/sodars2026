<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Core\Traits\HasUuid;
use App\Platform\Identity\Infrastructure\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletActivity extends Model
{
    use HasUuid;
    use BelongsToOrganization;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'wallet_activities';

    protected $fillable = [
        'id',
        'organization_id',
        'wallet_id',
        'performed_by',
        'action',
        'description',
        'trace_id',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}
