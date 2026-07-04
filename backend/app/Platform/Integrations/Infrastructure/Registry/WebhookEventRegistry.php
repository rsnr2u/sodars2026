<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Infrastructure\Registry;

class WebhookEventRegistry
{
    public const BOOKING_CREATED = 'booking.created';
    public const BOOKING_STATUS_CHANGED = 'booking.status_changed';
    public const INVOICE_CREATED = 'invoice.created';
    public const INVENTORY_CREATED = 'inventory.created';
    public const INVENTORY_UPDATED = 'inventory.updated';
    public const INVENTORY_DELETED = 'inventory.deleted';
    public const INVENTORY_STATUS_CHANGED = 'inventory.status_changed';

    public const CRM_LEAD_CREATED = 'crm.lead.created';
    public const CRM_LEAD_STATUS_CHANGED = 'crm.lead.status_changed';
    public const CRM_OPPORTUNITY_CREATED = 'crm.opportunity.created';
    public const CRM_OPPORTUNITY_STAGE_CHANGED = 'crm.opportunity.stage_changed';
    public const CRM_QUOTATION_CREATED = 'crm.quotation.created';
    public const CRM_QUOTATION_STATUS_CHANGED = 'crm.quotation.status_changed';

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
            self::INVENTORY_UPDATED,
            self::INVENTORY_DELETED,
            self::INVENTORY_STATUS_CHANGED,
            self::CRM_LEAD_CREATED,
            self::CRM_LEAD_STATUS_CHANGED,
            self::CRM_OPPORTUNITY_CREATED,
            self::CRM_OPPORTUNITY_STAGE_CHANGED,
            self::CRM_QUOTATION_CREATED,
            self::CRM_QUOTATION_STATUS_CHANGED,
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
