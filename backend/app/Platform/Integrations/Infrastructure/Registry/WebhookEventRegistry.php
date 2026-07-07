<?php

declare(strict_types=1);

namespace App\Platform\Integrations\Infrastructure\Registry;

class WebhookEventRegistry
{
    public const BOOKING_CREATED = 'booking.created';
    public const BOOKING_STATUS_CHANGED = 'booking.status_changed';
    public const FINANCE_INVOICE_CREATED = 'finance.invoice.created';
    public const FINANCE_INVOICE_ISSUED = 'finance.invoice.issued';
    public const FINANCE_INVOICE_PAID = 'finance.invoice.paid';
    public const FINANCE_INVOICE_VOIDED = 'finance.invoice.voided';
    public const FINANCE_PAYMENT_RECEIVED = 'finance.payment.received';
    public const FINANCE_PAYMENT_FAILED = 'finance.payment.failed';
    public const FINANCE_PAYMENT_REFUNDED = 'finance.payment.refunded';
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

    public const PROVIDER_CREATED = 'provider.created';
    public const PROVIDER_UPDATED = 'provider.updated';
    public const PROVIDER_VERIFIED = 'provider.verified';
    public const PROVIDER_SUSPENDED = 'provider.suspended';
    public const PROVIDER_SUBSCRIPTION_CHANGED = 'provider.subscription.changed';

    public const CAMPAIGN_CREATED = 'campaign.created';
    public const CAMPAIGN_UPDATED = 'campaign.updated';
    public const CAMPAIGN_STATUS_CHANGED = 'campaign.status_changed';
    public const CAMPAIGN_CREATIVE_ADDED = 'campaign.creative.added';
    public const CAMPAIGN_PROOF_UPLOADED = 'campaign.proof.uploaded';

    public const WALLET_CREATED = 'wallet.created';
    public const WALLET_DEPOSITED = 'wallet.deposited';
    public const WALLET_WITHDRAWN = 'wallet.withdrawn';
    public const WALLET_TRANSFERRED = 'wallet.transferred';
    public const WALLET_SETTLEMENT_CREDITED = 'wallet.settlement_credited';

    public const TRANSPORT_VEHICLE_CREATED = 'transport.vehicle.created';
    public const TRANSPORT_ROUTE_DISPATCHED = 'transport.route.dispatched';
    public const TRANSPORT_ROUTE_COMPLETED = 'transport.route.completed';
    public const TRANSPORT_VEHICLE_MAINTENANCE_COMPLETED = 'transport.vehicle.maintenance.completed';
    public const TRANSPORT_DRIVER_SUSPENDED = 'transport.driver.suspended';
    public const TRANSPORT_FUEL_LOGGED = 'transport.fuel.logged';

    public const IOT_DEVICE_REGISTERED = 'iot.device.registered';
    public const IOT_DEVICE_ONLINE = 'iot.device.online';
    public const IOT_DEVICE_OFFLINE = 'iot.device.offline';
    public const IOT_COMMAND_COMPLETED = 'iot.command.completed';
    public const IOT_ALERT_RAISED = 'iot.alert.raised';
    public const IOT_FIRMWARE_INSTALLED = 'iot.firmware.installed';

    // Operations Webhooks
    public const OPERATIONS_SCHEDULE_CREATED = 'operations.schedule.created';
    public const OPERATIONS_SCHEDULE_DISPATCHED = 'operations.schedule.dispatched';
    public const OPERATIONS_SCHEDULE_COMPLETED = 'operations.schedule.completed';
    public const OPERATIONS_RESOURCE_ASSIGNED = 'operations.resource.assigned';
    public const OPERATIONS_CONFLICT_DETECTED = 'operations.conflict.detected';

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
            self::FINANCE_INVOICE_CREATED,
            self::FINANCE_INVOICE_ISSUED,
            self::FINANCE_INVOICE_PAID,
            self::FINANCE_INVOICE_VOIDED,
            self::FINANCE_PAYMENT_RECEIVED,
            self::FINANCE_PAYMENT_FAILED,
            self::FINANCE_PAYMENT_REFUNDED,
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
            self::PROVIDER_CREATED,
            self::PROVIDER_UPDATED,
            self::PROVIDER_VERIFIED,
            self::PROVIDER_SUSPENDED,
            self::PROVIDER_SUBSCRIPTION_CHANGED,
            self::CAMPAIGN_CREATED,
            self::CAMPAIGN_UPDATED,
            self::CAMPAIGN_STATUS_CHANGED,
            self::CAMPAIGN_CREATIVE_ADDED,
            self::CAMPAIGN_PROOF_UPLOADED,
            self::WALLET_CREATED,
            self::WALLET_DEPOSITED,
            self::WALLET_WITHDRAWN,
            self::WALLET_TRANSFERRED,
            self::WALLET_SETTLEMENT_CREDITED,
            self::TRANSPORT_VEHICLE_CREATED,
            self::TRANSPORT_ROUTE_DISPATCHED,
            self::TRANSPORT_ROUTE_COMPLETED,
            self::TRANSPORT_VEHICLE_MAINTENANCE_COMPLETED,
            self::TRANSPORT_DRIVER_SUSPENDED,
            self::TRANSPORT_FUEL_LOGGED,
            self::IOT_DEVICE_REGISTERED,
            self::IOT_DEVICE_ONLINE,
            self::IOT_DEVICE_OFFLINE,
            self::IOT_COMMAND_COMPLETED,
            self::IOT_ALERT_RAISED,
            self::IOT_FIRMWARE_INSTALLED,
            self::OPERATIONS_SCHEDULE_CREATED,
            self::OPERATIONS_SCHEDULE_DISPATCHED,
            self::OPERATIONS_SCHEDULE_COMPLETED,
            self::OPERATIONS_RESOURCE_ASSIGNED,
            self::OPERATIONS_CONFLICT_DETECTED,
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
