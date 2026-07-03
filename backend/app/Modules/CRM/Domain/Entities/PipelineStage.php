<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseModel;

class PipelineStage extends BaseModel
{
    protected $table = 'crm_pipeline_stages';

    protected $fillable = [
        'name',
        'display_order',
        'probability',
        'is_closed',
        'is_won',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'probability' => 'integer',
        'is_closed' => 'boolean',
        'is_won' => 'boolean',
    ];
}
