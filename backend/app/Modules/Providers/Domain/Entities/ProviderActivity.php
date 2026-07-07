<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderActivity extends BaseBusinessModel
{
    protected $table = 'provider_activities';

    protected $fillable = [
        'organization_id',
        'provider_id',
        'activity_type',
        'description',
        'causation_id',
        'correlation_id',
        'trace_id',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
