<?php

declare(strict_types=1);

namespace App\Modules\Providers\Domain\Entities;

use App\Core\Models\BaseBusinessModel;
use App\Models\User;
use App\Modules\Providers\Domain\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderDocument extends BaseBusinessModel
{
    protected $table = 'provider_documents';

    protected $fillable = [
        'organization_id',
        'provider_id',
        'asset_id',
        'document_type',
        'status',
        'version',
        'is_current',
        'replaced_by',
        'supersedes',
        'verified_by',
        'verified_at',
        'remarks',
        'expires_at',
    ];

    protected $casts = [
        'status' => DocumentStatus::class,
        'version' => 'integer',
        'is_current' => 'boolean',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function replacedByDocument(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaced_by');
    }

    public function supersededDocument(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(\App\Platform\DAM\Domain\Entities\Asset::class, 'asset_id');
    }
}
