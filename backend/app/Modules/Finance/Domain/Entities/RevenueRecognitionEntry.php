<?php

declare(strict_types=1);

namespace App\Modules\Finance\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevenueRecognitionEntry extends BaseModel
{
    protected $table = 'revenue_recognition_entries';

    protected $fillable = [
        'id',
        'schedule_id',
        'recognition_date',
        'amount_cents',
        'status',
    ];

    protected $casts = [
        'recognition_date' => 'date',
        'amount_cents' => 'integer',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(RevenueRecognitionSchedule::class, 'schedule_id');
    }
}
