<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\CRM\Domain\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Quotation extends BaseModel
{
    protected $table = 'crm_quotations';

    protected $fillable = [
        'opportunity_id',
        'account_id',
        'quotation_number',
        'status',
        'active_version_number',
    ];

    protected $casts = [
        'status' => QuotationStatus::class,
        'active_version_number' => 'integer',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class, 'opportunity_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(QuotationVersion::class, 'quotation_id');
    }

    public function activeVersion(): HasOne
    {
        return $this->hasOne(QuotationVersion::class, 'quotation_id')
            ->where('is_active', true);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(CrmActivity::class, 'activityable');
    }
}
