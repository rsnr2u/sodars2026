<?php

declare(strict_types=1);

namespace App\Platform\DAM\Domain\Entities;

use App\Core\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends BaseModel
{
    protected $table = 'dam_collections';

    protected $fillable = [
        'name',
        'description',
    ];

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'dam_collection_asset', 'collection_id', 'asset_id');
    }
}
