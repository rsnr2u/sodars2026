<?php

declare(strict_types=1);

namespace App\Platform\DAM\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAi extends Model
{
    protected $table = 'dam_asset_ai';

    protected $fillable = [
        'asset_id',
        'caption',
        'ocr_text',
        'objects',
        'labels',
        'dominant_colors',
        'faces_detected',
    ];

    protected $casts = [
        'objects' => 'array',
        'labels' => 'array',
        'dominant_colors' => 'array',
        'faces_detected' => 'array',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }
}
