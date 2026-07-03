<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Models\User;
use App\Modules\CRM\Domain\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lead extends BaseModel
{
    protected $table = 'crm_leads';

    protected $fillable = [
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
}
