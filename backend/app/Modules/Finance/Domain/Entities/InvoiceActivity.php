<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Core\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceActivity extends Model
{
    use HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'invoice_activities';

    protected $fillable = [
        'id',
        'invoice_id',
        'performed_by',
        'action',
        'description',
        'ip',
        'user_agent',
        'trace_id',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
