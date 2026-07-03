<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Pipelines\Stages;

use App\Modules\Inventory\Domain\Entities\InventoryAvailability;
use Closure;

class CreateAvailabilityStage
{
    /**
     * Set up default availability slots.
     */
    public function handle(array $passable, Closure $next): mixed
    {
        $inventory = $passable['inventory'];

        $faces = $inventory->faces;
        foreach ($faces as $face) {
            InventoryAvailability::create([
                'inventory_face_id' => $face->id,
                'start_at' => now(),
                'end_at' => now()->addYears(50),
                'availability_status' => 'operational',
                'reason' => 'Operational',
                'source' => 'System',
                'remarks' => 'Default operational slot created on listing setup.',
            ]);
        }

        return $next($passable);
    }
}
