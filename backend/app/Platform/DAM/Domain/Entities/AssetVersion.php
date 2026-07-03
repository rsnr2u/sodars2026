<?php

declare(strict_types=1);

namespace App\Platform\DAM\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetVersion extends BaseModel
{
    protected $table = 'dam_asset_versions';

    protected $fillable = [
        'asset_id',
        'file_id',
        'version_number',
    ];

    protected $casts = [
        'version_number' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'file_id');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(AssetConversion::class, 'version_id');
    }
}
