<?php

declare(strict_types=1);

namespace App\Modules\CRM\Domain\Entities;

use App\Core\Models\BaseModel;

class LostReason extends BaseModel
{
    protected $table = 'crm_lost_reasons';

    protected $fillable = [
        'name',
        'description',
    ];
}
