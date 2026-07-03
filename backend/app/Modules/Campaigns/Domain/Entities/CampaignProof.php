<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Domain\Entities;

use App\Core\Models\BaseModel;
use App\Modules\Campaigns\Domain\Enums\ProofStatus;
use App\Modules\Inventory\Domain\Entities\InventoryFace;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignProof extends BaseModel
{
    protected $table = 'campaign_proofs';

    protected $fillable = [
        'campaign_id',
        'inventory_face_id',
        'file_path',
        'notes',
        'uploaded_by',
        'status',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'status' => ProofStatus::class,
        'verified_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function face(): BelongsTo
    {
        return $this->belongsTo(InventoryFace::class, 'inventory_face_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
