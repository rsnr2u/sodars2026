<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Infrastructure\Registry;

class WebhookEventRegistry
{
    public const BOOKING_CREATED = 'booking.created';
    public const BOOKING_STATUS_CHANGED = 'booking.status_changed';
    public const INVOICE_CREATED = 'invoice.created';
    public const INVENTORY_CREATED = 'inventory.created';

    /**
     * Get all supported webhook events.
     *
     * @return array<int, string>
     */
    public static function getEvents(): array
    {
        return [
            self::BOOKING_CREATED,
            self::BOOKING_STATUS_CHANGED,
            self::INVOICE_CREATED,
            self::INVENTORY_CREATED,
        ];
    }

    /**
     * Verify if event is supported.
     */
    public static function isValid(string $event): bool
    {
        return in_array($event, self::getEvents(), true);
    }
}
