<?php

declare(strict_types=1);

namespace App\Platform\DAM\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetConversion extends Model
{
    protected $table = 'dam_asset_conversions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'asset_id',
        'version_id',
        'file_id',
        'conversion_name',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(AssetVersion::class, 'version_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'file_id');
    }
}
