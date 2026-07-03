<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Providers\Domain\Enums\ContactType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderContact extends BaseModel
{
    protected $table = 'provider_contacts';

    protected $fillable = [
        'provider_id',
        'contact_name',
        'email',
        'phone',
        'type',
    ];

    protected $casts = [
        'type' => ContactType::class,
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
}
