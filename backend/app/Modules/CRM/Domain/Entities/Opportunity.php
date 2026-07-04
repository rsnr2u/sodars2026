<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Platform\Search\Domain\Contracts\Searchable;

class Opportunity extends BaseBusinessModel implements Searchable
{
    protected $table = 'crm_opportunities';

    protected $fillable = [
        'organization_id',
        'account_id',
        'contact_id',
        'title',
        'estimated_value_cents',
        'probability',
        'expected_value_cents',
        'pipeline_stage_id',
        'lost_reason_id',
        'close_date',
        'assigned_to',
    ];

    protected $casts = [
        'estimated_value_cents' => 'integer',
        'probability' => 'integer',
        'expected_value_cents' => 'integer',
        'close_date' => 'date',
    ];

    protected function beforeSave(): void
    {
        parent::beforeSave();
        $this->expected_value_cents = (int) round(($this->estimated_value_cents * $this->probability) / 100);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function lostReason(): BelongsTo
    {
        return $this->belongsTo(LostReason::class, 'lost_reason_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'opportunity_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'opportunity_id');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(CrmActivity::class, 'activityable');
    }

    public function toSearchDocument(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'estimated_value_cents' => $this->estimated_value_cents,
            'probability' => $this->probability,
            'expected_value_cents' => $this->expected_value_cents,
            'pipeline_stage_id' => $this->pipeline_stage_id,
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'crm_opportunities';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'title' => 'text',
            'pipeline_stage_id' => 'keyword',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['pipeline_stage_id'];
    }
}
