<?php

declare(strict_types=1);

namespace App\Platform\DAM\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $table = 'dam_tags';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
    ];

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'dam_asset_tag', 'tag_id', 'asset_id');
    }
}
