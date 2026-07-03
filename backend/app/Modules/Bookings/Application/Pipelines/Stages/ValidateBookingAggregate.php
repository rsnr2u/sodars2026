<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\Pipelines\Stages;

use App\Modules\Bookings\Domain\Services\BookingAggregateValidator;
use Closure;

class ValidateBookingAggregate
{
    public function __construct(
        protected BookingAggregateValidator $validator
    ) {}

    public function handle(array $passable, Closure $next): mixed
    {
        $dto = $passable['dto'];
        $customer = $passable['customer'];

        // Map items back to raw array layout for validator checks
        $itemsArray = array_map(fn($item) => [
            'inventory_face_id' => $item->inventoryFaceId,
            'start_date' => $item->startDate,
            'end_date' => $item->endDate,
        ], $dto->items);

        $this->validator->validate($customer, $dto->branchId, $dto->campaignId, $itemsArray);

        return $next($passable);
    }
}
