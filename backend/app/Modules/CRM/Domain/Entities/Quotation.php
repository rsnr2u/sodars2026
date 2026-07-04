<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Modules\CRM\Domain\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Platform\Search\Domain\Contracts\Searchable;

class Quotation extends BaseBusinessModel implements Searchable
{
    protected $table = 'crm_quotations';

    protected $fillable = [
        'organization_id',
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

    public function toSearchDocument(): array
    {
        return [
            'id' => $this->id,
            'quotation_number' => $this->quotation_number,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'opportunity_id' => $this->opportunity_id,
            'account_id' => $this->account_id,
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'crm_quotations';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'quotation_number' => 'text',
            'status' => 'keyword',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status'];
    }
}
