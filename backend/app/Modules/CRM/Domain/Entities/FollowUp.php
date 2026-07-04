<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Models\User;
use App\Modules\CRM\Domain\Enums\FollowUpStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends BaseBusinessModel
{
    protected $table = 'crm_followups';

    protected $fillable = [
        'organization_id',
        'lead_id',
        'opportunity_id',
        'assigned_to',
        'task_description',
        'recurrence',
        'due_at',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => FollowUpStatus::class,
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class, 'opportunity_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
