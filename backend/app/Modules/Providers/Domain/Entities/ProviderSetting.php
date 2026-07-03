<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Providers\Domain\ValueObjects\ProviderSettings;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderSetting extends BaseModel
{
    protected $table = 'provider_settings';

    protected $fillable = [
        'provider_id',
        'settings',
    ];

    protected $casts = [
        'settings' => ProviderSettings::class,
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }
}
