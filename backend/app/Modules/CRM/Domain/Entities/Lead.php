<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Models\User;
use App\Modules\CRM\Domain\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Platform\Search\Domain\Contracts\Searchable;

class Lead extends BaseBusinessModel implements Searchable
{
    protected $table = 'crm_leads';

    protected $fillable = [
        'organization_id',
        'account_id',
        'contact_id',
        'title',
        'source',
        'status',
        'lead_score',
        'assigned_to',
    ];

    protected $casts = [
        'status' => LeadStatus::class,
        'lead_score' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'lead_id');
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
            'source' => $this->source,
            'status' => $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status,
            'lead_score' => $this->lead_score,
            'organization_id' => $this->organization_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    public static function getSearchIndexName(): string
    {
        return 'crm_leads';
    }

    public static function getSearchFieldMappings(): array
    {
        return [
            'title' => 'text',
            'source' => 'keyword',
            'status' => 'keyword',
        ];
    }

    public static function getSearchFacetFields(): array
    {
        return ['status', 'source'];
    }
}
