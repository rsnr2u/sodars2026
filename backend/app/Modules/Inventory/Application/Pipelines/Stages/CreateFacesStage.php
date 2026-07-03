<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Pipelines\Stages;

use App\Modules\Inventory\Domain\Entities\InventoryFace;
use Closure;

class CreateFacesStage
{
    /**
     * Create child face units.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $inventory = $passable['inventory'];

        $facesData = $dto->faces;
        if (empty($facesData)) {
            // Add a default face
            $facesData = [[
                'face_code' => $inventory->inventory_code . '-F1',
                'display_name' => 'Default Face',
                'facing_direction' => 'north',
                'display_order' => 1,
                'physical_specifications' => [
                    'width_cm' => 1000,
                    'height_cm' => 500,
                    'orientation' => 'landscape',
                    'illuminated' => true,
                ],
            ]];
        }

        foreach ($facesData as $face) {
            InventoryFace::create([
                'inventory_id' => $inventory->id,
                'face_code' => $face['face_code'],
                'display_name' => $face['display_name'],
                'facing_direction' => $face['facing_direction'],
                'display_order' => (int) ($face['display_order'] ?? 1),
                'physical_specifications' => $face['physical_specifications'] ?? [],
                'is_active' => true,
            ]);
        }

        return $next($passable);
    }
}
