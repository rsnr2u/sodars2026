<?php

declare(strict_types=1);

namespace App\Platform\Search\Application\Listeners;

use App\Platform\Search\Application\Jobs\UpdateIndexDocumentJob;
use Illuminate\Contracts\Events\Dispatcher;

class SearchIndexListener
{
    /**
     * Handle incoming domain event generically.
     */
    public function handle(object $event): void
    {
        if (!$event instanceof \App\Core\Events\BusinessEvent) {
            return;
        }

        $entityId = $event->aggregateId;
        $entityClass = $event->getEntityClass();

        // Dynamically resolve index name from the database search_indexes table
        try {
            $indexName = \App\Platform\Search\Domain\Entities\SearchIndex::where('entity_type', $entityClass)->value('name');
        } catch (\Throwable $e) {
            $indexName = null;
        }

        // Fallback to static mappings if database is not populated yet (e.g. in test setup)
        if (!$indexName) {
            $fallbacks = [
                \App\Modules\Bookings\Domain\Entities\Booking::class => 'bookings',
                \App\Modules\Inventory\Domain\Entities\Inventory::class => 'inventories',
                \App\Modules\Finance\Domain\Entities\Invoice::class => 'finance_invoices',
                \App\Modules\Finance\Domain\Entities\Payment::class => 'finance_payments',
                \App\Modules\CRM\Domain\Entities\Lead::class => 'crm_leads',
                \App\Modules\CRM\Domain\Entities\Opportunity::class => 'crm_opportunities',
                \App\Modules\CRM\Domain\Entities\Quotation::class => 'crm_quotations',
                \App\Modules\Providers\Domain\Entities\Provider::class => 'provider_providers',
                \App\Modules\Campaigns\Domain\Entities\Campaign::class => 'campaign_campaigns',
                \App\Modules\Wallet\Domain\Entities\Wallet::class => 'wallet_wallets',
                \App\Modules\Transport\Domain\Entities\Vehicle::class => 'transport_vehicles',
                \App\Modules\Transport\Domain\Entities\Driver::class => 'transport_drivers',
                \App\Modules\Transport\Domain\Entities\Route::class => 'transport_routes',
                \App\Modules\IoT\Domain\Entities\Device::class => 'iot_devices',
                \App\Modules\IoT\Domain\Entities\DeviceCommand::class => 'iot_commands',
                \App\Modules\IoT\Domain\Entities\DeviceAlert::class => 'iot_alerts',
            ];
            $indexName = $fallbacks[$entityClass] ?? null;
        }

        if (!$indexName) {
            return;
        }

        $eventClass = get_class($event);
        if (str_contains($eventClass, 'Deleted')) {
            UpdateIndexDocumentJob::dispatch('remove', null, $entityId, $indexName);
        } else {
            UpdateIndexDocumentJob::dispatch('index', $entityClass, $entityId);
        }
    }

    /**
     * Register listeners.
     */
    public function subscribe(Dispatcher $events): void
    {
        $observedEvents = [
            // Bookings
            \App\Modules\Bookings\Domain\Events\BookingCreated::class,
            \App\Modules\Bookings\Domain\Events\BookingStatusChanged::class,
            // Finance
            \App\Modules\Finance\Domain\Events\InvoiceCreated::class,
            \App\Modules\Finance\Domain\Events\InvoiceIssued::class,
            \App\Modules\Finance\Domain\Events\InvoicePaid::class,
            \App\Modules\Finance\Domain\Events\InvoiceVoided::class,
            \App\Modules\Finance\Domain\Events\PaymentReceived::class,
            \App\Modules\Finance\Domain\Events\PaymentFailed::class,
            // Inventory
            \App\Modules\Inventory\Domain\Events\InventoryCreated::class,
            \App\Modules\Inventory\Domain\Events\InventoryUpdated::class,
            \App\Modules\Inventory\Domain\Events\InventoryDeleted::class,
            \App\Modules\Inventory\Domain\Events\InventoryApproved::class,
            \App\Modules\Inventory\Domain\Events\InventorySuspended::class,
            \App\Modules\Inventory\Domain\Events\InventoryStatusChanged::class,
            // CRM
            \App\Modules\CRM\Domain\Events\LeadCreated::class,
            \App\Modules\CRM\Domain\Events\LeadStatusChanged::class,
            \App\Modules\CRM\Domain\Events\OpportunityCreated::class,
            \App\Modules\CRM\Domain\Events\OpportunityStageChanged::class,
            \App\Modules\CRM\Domain\Events\QuotationCreated::class,
            \App\Modules\CRM\Domain\Events\QuotationStatusChanged::class,
            // Providers
            \App\Modules\Providers\Domain\Events\ProviderCreated::class,
            \App\Modules\Providers\Domain\Events\ProviderUpdated::class,
            \App\Modules\Providers\Domain\Events\ProviderVerified::class,
            \App\Modules\Providers\Domain\Events\ProviderSuspended::class,
            // Campaigns
            \App\Modules\Campaigns\Domain\Events\CampaignCreated::class,
            \App\Modules\Campaigns\Domain\Events\CampaignUpdated::class,
            \App\Modules\Campaigns\Domain\Events\CampaignApproved::class,
            \App\Modules\Campaigns\Domain\Events\CampaignScheduled::class,
            \App\Modules\Campaigns\Domain\Events\CampaignStarted::class,
            \App\Modules\Campaigns\Domain\Events\CampaignPaused::class,
            \App\Modules\Campaigns\Domain\Events\CampaignCompleted::class,
            \App\Modules\Campaigns\Domain\Events\CampaignCancelled::class,
            // Wallet
            \App\Modules\Wallet\Domain\Events\WalletCreated::class,
            \App\Modules\Wallet\Domain\Events\WalletActivated::class,
            \App\Modules\Wallet\Domain\Events\WalletSuspended::class,
            // Transport
            \App\Modules\Transport\Domain\Events\VehicleCreated::class,
            \App\Modules\Transport\Domain\Events\VehicleUpdated::class,
            \App\Modules\Transport\Domain\Events\VehicleStatusChanged::class,
            \App\Modules\Transport\Domain\Events\DriverCreated::class,
            \App\Modules\Transport\Domain\Events\DriverUpdated::class,
            \App\Modules\Transport\Domain\Events\RouteCreated::class,
            \App\Modules\Transport\Domain\Events\RouteStatusChanged::class,
            \App\Modules\Transport\Domain\Events\RouteDispatched::class,
            \App\Modules\Transport\Domain\Events\RouteCompleted::class,
            \App\Modules\Transport\Domain\Events\RouteCancelled::class,
            // IoT
            \App\Modules\IoT\Domain\Events\DeviceRegistered::class,
            \App\Modules\IoT\Domain\Events\DeviceActivated::class,
            \App\Modules\IoT\Domain\Events\DeviceSuspended::class,
            \App\Modules\IoT\Domain\Events\DeviceCommandQueued::class,
            \App\Modules\IoT\Domain\Events\DeviceCommandCompleted::class,
            \App\Modules\IoT\Domain\Events\DeviceAlertRaised::class,
            \App\Modules\IoT\Domain\Events\DeviceAlertResolved::class,
        ];

        foreach ($observedEvents as $eventClass) {
            $events->listen($eventClass, [self::class, 'handle']);
        }
    }
}
