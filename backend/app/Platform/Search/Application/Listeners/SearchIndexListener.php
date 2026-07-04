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
                \App\Modules\Finance\Domain\Entities\Invoice::class => 'invoices',
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
            \App\Modules\Finance\Domain\Events\InvoiceVoided::class,
            // Inventory
            \App\Modules\Inventory\Domain\Events\InventoryCreated::class,
            \App\Modules\Inventory\Domain\Events\InventoryUpdated::class,
            \App\Modules\Inventory\Domain\Events\InventoryDeleted::class,
            \App\Modules\Inventory\Domain\Events\InventoryApproved::class,
            \App\Modules\Inventory\Domain\Events\InventorySuspended::class,
            \App\Modules\Inventory\Domain\Events\InventoryStatusChanged::class,
        ];

        foreach ($observedEvents as $eventClass) {
            $events->listen($eventClass, [self::class, 'handle']);
        }
    }
}
