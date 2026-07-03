<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Opportunity extends BaseModel
{
    protected $table = 'crm_opportunities';

    protected $fillable = [
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

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Opportunity $model) {
            $model->expected_value_cents = (int) round(($model->estimated_value_cents * $model->probability) / 100);
        });
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
}
