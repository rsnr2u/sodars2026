<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\DTOs;

class BookingItemData
{
    public function __construct(
        public readonly string $inventoryFaceId,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly int $dailyFrequency = 1
    ) {}
}
