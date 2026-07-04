<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CrmActivity extends BaseBusinessModel
{
    protected $table = 'crm_activities';

    protected $fillable = [
        'organization_id',
        'activityable_type',
        'activityable_id',
        'performed_by',
        'activity_type',
        'description',
    ];

    public function activityable(): MorphTo
    {
        return $this->morphTo();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
