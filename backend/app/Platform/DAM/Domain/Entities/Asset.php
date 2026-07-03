<?php

declare(strict_types=1);

namespace App\Platform\DAM\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Platform\DAM\Domain\Enums\AssetStatus;
use App\Platform\DAM\Domain\Enums\AssetType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asset extends BaseModel
{
    protected $table = 'dam_assets';

    protected $fillable = [
        'folder_id',
        'current_version_id',
        'title',
        'description',
        'asset_type',
        'status',
        'attachment_count',
        'download_count',
        'view_count',
        'archived_at',
    ];

    protected $casts = [
        'status' => AssetStatus::class,
        'asset_type' => AssetType::class,
        'attachment_count' => 'integer',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'archived_at' => 'datetime',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(AssetVersion::class, 'current_version_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AssetVersion::class, 'asset_id')->orderBy('version_number', 'asc');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'dam_asset_tag', 'asset_id', 'tag_id');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'dam_collection_asset', 'asset_id', 'collection_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'asset_id');
    }

    public function ai(): HasOne
    {
        return $this->hasOne(AssetAi::class, 'asset_id');
    }
}
