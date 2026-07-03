<?php

declare(strict_types=1);

namespace App\Platform\Reporting\Domain\Entities;

use App\Core\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledReport extends Model
{
    use HasUuid;

    protected $table = 'scheduled_reports';

    protected $fillable = [
        'user_id',
        'report_key',
        'name',
        'cron_expression',
        'query_parameters',
        'recipient_emails',
        'export_format',
        'is_active',
        'last_run_at',
    ];

    protected $casts = [
        'query_parameters' => 'array',
        'recipient_emails' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
