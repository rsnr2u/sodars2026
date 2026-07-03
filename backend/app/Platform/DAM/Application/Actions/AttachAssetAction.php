<?php

declare(strict_types=1);

namespace App\Platform\DAM\Application\Actions;

use App\Platform\DAM\Domain\Entities\Asset;
use App\Platform\DAM\Domain\Entities\Attachment;
use App\Platform\DAM\Domain\Enums\AttachmentRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttachAssetAction
{
    /**
     * Link an asset polymorphically to an ERP business entity.
     */
    public function execute(string $assetId, Model $entity, AttachmentRole $role): Attachment
    {
        return DB::transaction(function () use ($assetId, $entity, $role) {
            $asset = Asset::findOrFail($assetId);

            // Create Attachment record
            $attachment = Attachment::create([
                'id' => (string) Str::uuid(),
                'asset_id' => $asset->id,
                'attachable_type' => get_class($entity),
                'attachable_id' => (string) $entity->getKey(),
                'attachment_role' => $role,
            ]);

            // Increment usage counters
            $asset->increment('attachment_count');

            return $attachment;
        });
    }
}
