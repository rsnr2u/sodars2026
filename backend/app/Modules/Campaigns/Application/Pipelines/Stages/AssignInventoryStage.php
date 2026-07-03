<?php

declare(strict_types=1);

namespace App\Modules\Campaigns\Application\Pipelines\Stages;

use App\Modules\Inventory\Domain\Entities\InventoryFace;
use Closure;
use Illuminate\Validation\ValidationException;

class AssignInventoryStage
{
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $campaign = $passable['campaign'];

        if (!empty($dto->inventoryFaceIds)) {
            foreach ($dto->inventoryFaceIds as $faceId) {
                $face = InventoryFace::find($faceId);
                if (!$face) {
                    throw ValidationException::withMessages([
                        'inventory_face_ids' => ["Target inventory face ID '{$faceId}' does not exist."],
                    ]);
                }

                // Associate in junction table
                $campaign->inventoryFaces()->attach($faceId, [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                ]);
            }
        }

        return $next($passable);
    }
}
