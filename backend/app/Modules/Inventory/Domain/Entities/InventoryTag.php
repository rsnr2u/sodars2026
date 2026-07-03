<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphedByMany;

class InventoryTag extends Model
{
    protected $table = 'inventory_tags';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function inventories(): MorphedByMany
    {
        return $this->morphedByMany(
            Inventory::class,
            'taggable',
            'inventory_taggables',
            'tag_id',
            'taggable_id'
        );
    }
}
