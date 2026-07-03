<?php

declare(strict_types=1);

namespace App\Modules\Bookings\Application\DTOs;

use Illuminate\Http\Request;

class CreateBookingData
{
    /**
     * @param array<int, BookingItemData> $items
     */
    public function __construct(
        public readonly string $customerId,
        public readonly string $branchId,
        public readonly array $items,
        public readonly ?string $campaignId = null,
        public readonly string $currency = 'INR'
    ) {}

    public static function fromRequest(Request $request): self
    {
        $itemsData = [];
        $items = $request->input('items', []);

        foreach ($items as $item) {
            $itemsData[] = new BookingItemData(
                inventoryFaceId: $item['inventory_face_id'],
                startDate: $item['start_date'],
                endDate: $item['end_date'],
                dailyFrequency: (int) ($item['daily_frequency'] ?? 1)
            );
        }

        return new self(
            customerId: $request->input('customer_id'),
            branchId: $request->input('branch_id'),
            items: $itemsData,
            campaignId: $request->input('campaign_id'),
            currency: $request->input('currency', 'INR')
        );
    }
}
