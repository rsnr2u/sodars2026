<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Pipelines\Stages;

use App\Modules\Inventory\Domain\Entities\InventoryAvailability;
use Closure;
use Illuminate\Support\Str;

class ReserveAvailability
{
    public function handle(array $passable, Closure $next): mixed
    {
        $booking = $passable['booking'];
        $items = $passable['items'];

        foreach ($items as $item) {
            // Reserve tentative/draft blocks on the availability ledger.
            // When approved, status shifts to "reserved". In draft, starts as "tentative".
            InventoryAvailability::create([
                'id' => (string) Str::uuid(),
                'inventory_face_id' => $item->inventory_face_id,
                'start_at' => $item->start_date->startOfDay(),
                'end_at' => $item->end_date->endOfDay(),
                'availability_status' => 'blocked',
                'reason' => "Booking created: {$booking->booking_code}",
                'source' => 'Booking',
            ]);
        }

        return $next($passable);
    }
}
