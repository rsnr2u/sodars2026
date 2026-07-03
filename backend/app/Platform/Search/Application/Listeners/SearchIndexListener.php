<?php

declare(strict_types=1);

namespace App\Platform\Search\Application\Listeners;

use App\Platform\Search\Application\Jobs\UpdateIndexDocumentJob;
use Illuminate\Contracts\Events\Dispatcher;

class SearchIndexListener
{
    /**
     * Handle incoming domain event.
     */
    public function handle(object $event): void
    {
        $eventClass = get_class($event);
        $entityId = $event->aggregateId ?? null;

        if (!$entityId) {
            return;
        }

        $entityClass = null;
        $indexName = null;

        if (str_contains($eventClass, 'Booking')) {
            $entityClass = \App\Modules\Bookings\Domain\Entities\Booking::class;
            $indexName = 'bookings';
        } elseif (str_contains($eventClass, 'Invoice')) {
            $entityClass = \App\Modules\Finance\Domain\Entities\Invoice::class;
            $indexName = 'invoices';
        } elseif (str_contains($eventClass, 'Inventory')) {
            $entityClass = \App\Modules\Inventory\Domain\Entities\Inventory::class;
            $indexName = 'inventories';
        }

        if (!$entityClass || !$indexName) {
            return;
        }

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
        ];

        foreach ($observedEvents as $eventClass) {
            $events->listen($eventClass, [self::class, 'handle']);
        }
    }
}
